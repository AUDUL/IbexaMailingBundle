<?php



declare(strict_types=1);

namespace CodeRhapsodie\Bundle\IbexaMailingBundle\Core\DataHandler;

use CodeRhapsodie\Bundle\IbexaMailingBundle\Entity\MailingList;
use CodeRhapsodie\Bundle\IbexaMailingBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;

class Registration
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

    /**
     * @return User
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @return $this
     */
    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return MailingList[]|ArrayCollection
     */
    public function getMailingLists()
    {
        return $this->mailingLists;
    }

    /**
     * @param MailingList[]|ArrayCollection $mailingLists
     *
     * @return $this
     */
    public function setMailingLists($mailingLists): self
    {
        $this->mailingLists = $mailingLists;

        return $this;
    }
}
