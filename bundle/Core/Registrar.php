<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Core;

use Carbon\Carbon;
use CodeRhapsodie\IbexaMailingBundle\Core\DataHandler\Registration;
use CodeRhapsodie\IbexaMailingBundle\Core\DataHandler\Unregistration;
use CodeRhapsodie\IbexaMailingBundle\Core\Mailer\Simple as SimpleMailer;
use CodeRhapsodie\IbexaMailingBundle\Entity\ConfirmationToken;
use CodeRhapsodie\IbexaMailingBundle\Entity\MailingList;
use CodeRhapsodie\IbexaMailingBundle\Entity\Registration as RegistrationEntity;
use CodeRhapsodie\IbexaMailingBundle\Entity\User;
use CodeRhapsodie\IbexaMailingBundle\Repository\ConfirmationTokenRepository;
use CodeRhapsodie\IbexaMailingBundle\Repository\MailingListRepository;
use CodeRhapsodie\IbexaMailingBundle\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\MVC\Symfony\SiteAccess;

class Registrar
{
    /**
     * 5 hours.
     */
    public const TOKEN_EXPIRATION_HOURS = 5;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SiteAccess $siteAccess,
        private readonly SimpleMailer $mailer,
        protected readonly ConfigResolverInterface $configResolver,
        private readonly UserRepository $userRepository,
        private readonly MailingListRepository $mailingListRepository,
        private readonly ConfirmationTokenRepository $confirmationTokenRepository
    ) {
    }

    public function askForConfirmation(Registration $registration): void
    {
        $user = $registration->getUser();
        if ($user === null) {
            throw new \RuntimeException('UserRepository cannot be empty.');
        }

        $fetchUser = $this->userRepository->findOneBy(['email' => $user->getEmail()]);

        if (!$fetchUser instanceof User) {
            $user->setStatus(User::PENDING);
            $user->setOrigin($this->siteAccess->name);
            $fetchUser = $user;
            $this->entityManager->persist($fetchUser);
            $this->entityManager->flush();
        }

        $token = $this->createConfirmationToken(
            ConfirmationToken::REGISTER,
            $fetchUser,
            $registration->getMailingLists()
        );
        $this->mailer->sendRegistrationConfirmation($registration, $token);
    }

    public function askForUnregisterConfirmation(Unregistration $unregistration): bool
    {
        $user = $unregistration->getUser();
        if ($user === null) {
            throw new \RuntimeException('UserRepository cannot be empty.');
        }
        $fetchUser = $this->userRepository->findOneBy(['email' => $user->getEmail()]);

        if (!$fetchUser instanceof User) {
            return false;
        }

        $token = $this->createConfirmationToken(
            ConfirmationToken::UNREGISTER,
            $fetchUser,
            $unregistration->getMailingLists()
        );

        $this->confirm($token);

        return true;
    }

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function confirm(ConfirmationToken $token): bool
    {
        $created = Carbon::instance($token->getCreated());
        $expired = Carbon::now()->subHours(static::TOKEN_EXPIRATION_HOURS);
        if ($created->lessThan($expired)) {
            return false;
        }

        ['action' => $action, 'userId' => $userId, 'mailingListIds' => $mailingListIds] = $token->getPayload();
        if (!\in_array($action, [ConfirmationToken::REGISTER, ConfirmationToken::UNREGISTER])) {
            return false;
        }
        $user = $this->userRepository->find($userId);
        if (!$user instanceof User) {
            return false;
        }

        $this->addMailingLists($mailingListIds, $action, $user);

        // in any case we can confirm the email here
        if ($user->isPending()) {
            $user->setStatus(User::CONFIRMED);
        }

        // if no more registration then we remove the user
        if ($user->getRegistrations()->isEmpty()) {
            if ($this->configResolver->getParameter('delete_user', 'ibexamailing')) {
                $this->entityManager->remove($user);
            } else {
                $user->setStatus(User::REMOVED);
            }
        }

        $this->entityManager->remove($token);
        $this->entityManager->flush();

        return true;
    }

    /**
     * Clean the ConfirmationTokenRepository expired records.
     */
    public function cleanup(): void
    {
        $criteria = new Criteria();
        $criteria->where(Criteria::expr()->lt('created', Carbon::now()->subHours(static::TOKEN_EXPIRATION_HOURS)));
        $results = $this->confirmationTokenRepository->matching($criteria);

        foreach ($results as $result) {
            $this->entityManager->remove($result);
        }
        $this->entityManager->flush();
    }

    /**
     * @return ArrayCollection<int, MailingList|null>
     */
    public function getDefaultMailingList(): ArrayCollection
    {
        $mailingListId = null;
        if ($this->configResolver->hasParameter('default_mailinglist_id', 'ibexamailing')) {
            $mailingListId = $this->configResolver->getParameter('default_mailinglist_id', 'ibexamailing');
        }
        $mailingList = $this->mailingListRepository->find($mailingListId);

        return new ArrayCollection([$mailingList]);
    }

    /**
     * @param array<MailingList>|ArrayCollection<int, MailingList> $mailingLists
     */
    private function createConfirmationToken(
        string $action,
        User $user,
        mixed $mailingLists
    ): ConfirmationToken {
        if ($mailingLists instanceof ArrayCollection) {
            $mailingLists = $mailingLists->toArray();
        }
        /** @var array<MailingList> $mailingListIds */
        $mailingListIds = array_map(function (MailingList $mailingList) {
            return $mailingList->getId();
        }, $mailingLists);

        $confirmationToken = new ConfirmationToken();
        $confirmationToken->setPayload(
            [
                'action' => $action,
                'userId' => $user->getId(),
                'mailingListIds' => $mailingListIds,
            ]
        );
        $this->entityManager->persist($confirmationToken);
        $this->entityManager->flush();

        return $confirmationToken;
    }

    /**
     * @param array<mixed> $mailingListIds
     */
    private function addMailingLists(array $mailingListIds, string $action, User $user): void
    {
        foreach ($mailingListIds as $id) {
            $mailingList = $this->mailingListRepository->find($id);
            if (!$mailingList instanceof MailingList) {
                continue;
            }

            if ($action == ConfirmationToken::REGISTER) {
                $registration = new RegistrationEntity();
                $registration->setApproved(!$mailingList->isWithApproval());
                $registration->setMailingList($mailingList);
                $user->addRegistration($registration);
            }

            if ($action == ConfirmationToken::UNREGISTER) {
                $currentRegistrations = $user->getRegistrations();
                foreach ($currentRegistrations as $registration) {
                    if ($registration->getMailingList()->getId() === $id) {
                        $user->removeRegistration($registration);
                    }
                }
            }
        }
    }
}
