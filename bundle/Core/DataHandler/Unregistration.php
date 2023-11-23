<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Core\DataHandler;

use CodeRhapsodie\IbexaMailingBundle\Entity\MailingList;
use CodeRhapsodie\IbexaMailingBundle\Entity\User;

class Unregistration
{
    private ?User $user;

    /**
     * @var array<MailingList>
     */
    private array $mailingLists;

    public function __construct()
    {
        $this->mailingLists = [];
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return MailingList[]
     */
    public function getMailingLists(): array
    {
        return $this->mailingLists;
    }

    /**
     * @param MailingList[] $mailingLists
     */
    public function setMailingLists(array $mailingLists): self
    {
        $this->mailingLists = $mailingLists;

        return $this;
    }
}
