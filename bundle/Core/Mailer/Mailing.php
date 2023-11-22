<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Core\Mailer;

use CodeRhapsodie\IbexaMailingBundle\Core\Provider\Broadcast;
use CodeRhapsodie\IbexaMailingBundle\Core\Provider\MailingContent;
use CodeRhapsodie\IbexaMailingBundle\Entity\Mailing as MailingEntity;
use CodeRhapsodie\IbexaMailingBundle\Entity\User;
use CodeRhapsodie\IbexaMailingBundle\Repository\UserRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Message;

/**
 * Class MailingRepository.
 */
class Mailing
{
    public function __construct(
        private readonly Simple $simpleMailer,
        private readonly MailingContent $contentProvider,
        private readonly LoggerInterface $logger,
        private readonly Broadcast $broadcastProvider,
        private readonly MailerInterface $mailer,
        private readonly string $mailing,
        private readonly UserRepository $userRepository,
        private readonly MailingProcess $mailingProcess
    ) {
    }

    public function sendMailing(MailingEntity $mailing, string $forceRecipient = null): void
    {
        $nativeHtml = $this->contentProvider->preFetchContent($mailing);
        $broadcast = $this->broadcastProvider->start($mailing, $nativeHtml);

        $this->simpleMailer->sendStartSendingMailingMessage($mailing);

        if ($forceRecipient) {
            $fakeUser = new User();
            $fakeUser->setEmail($forceRecipient);
            $fakeUser->setFirstName('XXXX');
            $fakeUser->setLastName('YYYY');
            $contentMessage = $this->contentProvider->getContentMailing($mailing, $fakeUser, $broadcast);
            $this->logger->debug("MailingRepository Mailer starts to test {$contentMessage->getSubject()}.");
            $this->sendMessage($contentMessage);
        } elseif (!$forceRecipient) {
            $campaign = $mailing->getCampaign();
            $this->logger->notice("MailingRepository Mailer starts to send Mailing {$mailing->getName()}");
            $recipientCounts = 0;
            $recipients = $this->userRepository->findValidRecipients($campaign->getMailingLists()->toArray());

            $this->mailingProcess->runParallelProcess($broadcast->getId(), $this->fetchIterationFromUserList($recipients, 10));

            // send copy of email
            $fakeUser = new User();
            $fakeUser->setEmail($mailing->getCampaign()->getReportEmail());
            $fakeUser->setFirstName('XXXX');
            $fakeUser->setLastName('YYYY');
            $contentMessage = $this->contentProvider->getContentMailing($mailing, $fakeUser, $broadcast);
            $this->sendMessage($contentMessage);

            $this->broadcastProvider->store($broadcast);
            $this->logger->notice("Mailing {$mailing->getName()} induced {$recipientCounts} emails sent.");
        }
        $this->simpleMailer->sendStopSendingMailingMessage($mailing);
        $this->broadcastProvider->end($broadcast);
    }

    public function sendMessage(Message $message): void
    {
        $message->getHeaders()->addTextHeader('X-Transport', $this->mailing);
        $this->mailer->send($message);
    }

    /**
     * @param array<User> $users
     */
    private function fetchIterationFromUserList(array $users, int $iterationCount): \Generator
    {
        do {
            $usersId = [];

            foreach ($users as $user) {
                $usersId[] = $user->getId();
                if (\count($usersId) === $iterationCount) {
                    $data = $usersId;
                    $usersId = [];
                    yield $data;
                }
            }

            if (!empty($usersId)) {
                yield $usersId;
            }
        } while (!empty($users));
    }
}
