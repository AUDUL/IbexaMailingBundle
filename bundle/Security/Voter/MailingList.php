<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Security\Voter;

use CodeRhapsodie\IbexaMailingBundle\Entity\MailingList as MailingListEntity;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Core\MVC\Symfony\SiteAccess;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class MailingList extends Voter
{
    public const VIEW = 'view';
    public const EDIT = 'edit';

    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var SiteAccess
     */
    private $siteAccess;

    public function __construct(Repository $repository, SiteAccess $siteAccess)
    {
        $this->repository = $repository;
        $this->siteAccess = $siteAccess;
    }

    protected function supports($attribute, $subject): bool
    {
        if (!\in_array($attribute, [self::VIEW, self::EDIT])) {
            return false;
        }

        if (!$subject instanceof MailingListEntity) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritDoc}
     *
     * @param MailingListEntity|null $subject
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        // all create
        if ($subject === null) {
            return true;
        }

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($subject);
            case self::EDIT:
                return $this->canEdit($subject);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView(MailingListEntity $subject): bool
    {
        $siteaccessLimist = $subject->getSiteaccessLimit();
        // if no limit then we vote OK
        if ($siteaccessLimist === null || \count($siteaccessLimist) === 0) {
            return true;
        }

        if (\in_array($this->siteAccess->name, $siteaccessLimist)) {
            return true;
        }

        // @improvment: maybe we should add a module/function for that specific purpose
        return $this->repository->getPermissionResolver()->hasAccess('setup', 'administrate');
    }

    private function canEdit(MailingListEntity $subject): bool
    {
        return $this->canView($subject);
    }
}
