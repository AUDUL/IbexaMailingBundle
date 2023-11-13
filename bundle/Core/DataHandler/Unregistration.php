<?php



declare(strict_types=1);

namespace CodeRhapsodie\Bundle\IbexaMailingBundle\Core\DataHandler;

use CodeRhapsodie\Bundle\IbexaMailingBundle\Entity\MailingList;
use CodeRhapsodie\Bundle\IbexaMailingBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;

class Unregistration
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var MailingList[]
     */
    private $mailingLists;

    public function __construct()
    {
        $this->mailingLists = new ArrayCollection();
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
     * @return MailingList[]|mixed
     */
    public function getMailingLists()
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
