<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Security\Voter;

use CodeRhapsodie\IbexaMailingBundle\Entity\Mailing as MailingEntity;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class Mailing extends Voter
{
    public const VIEW = 'view';
    public const EDIT = 'edit';

    /**
     * @var AccessDecisionManagerInterface
     */
    private $decisionManager;

    public function __construct(AccessDecisionManagerInterface $decisionManager)
    {
        $this->decisionManager = $decisionManager;
    }

    protected function supports($attribute, $subject): bool
    {
        if (!\in_array($attribute, [self::VIEW, self::EDIT])) {
            return false;
        }

        if (!$subject instanceof MailingEntity) {
            return false;
        }

        return true;
    }

    /**
     * @param MailingEntity $subject
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        return $this->decisionManager->decide($token, [$attribute], $subject->getCampaign());
    }
}
