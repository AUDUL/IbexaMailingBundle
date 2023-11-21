<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * A BroadcastRepository is a record of a MailingRepository "sending" at a certain point in time
 * to a certain number of approved registrations
 * with a certain html contents (we will backup here)
 * It's really a record of a MailingRepository broadcast.
 *
 * @ORM\Table(name="mailing_broadcast")
 *
 * @ORM\Entity(repositoryClass="CodeRhapsodie\IbexaMailingBundle\Repository\BroadcastRepository")
 */
class Broadcast
{
    use Compose\Metadata;

    /**
     * @var int
     *
     * @ORM\Column(name="BDCST_id", type="bigint", nullable=false)
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="BDCST_started", type="datetime", nullable=false)
     */
    private $started;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="BDCST_ended", type="datetime", nullable=true)
     */
    private $ended;

    /**
     * @var int
     *
     * @ORM\Column(name="BDCST_email_sent_count", type="integer", nullable=false)
     */
    private $emailSentCount;

    /**
     * @var string
     *
     * @ORM\Column(name="BDCST_html", type="text", nullable=false)
     */
    private $html;

    /**
     * @var Mailing
     *
     * @ORM\ManyToOne(targetEntity="MailingRepository", inversedBy="broadcasts")
     *
     * @ORM\JoinColumn(name="MAIL_id", referencedColumnName="MAIL_id")
     */
    private $mailing;

    /**
     * @var StatHit[]
     *
     * @ORM\OneToMany(targetEntity="StatHitRepository", mappedBy="broadcast",
     *                                                                                cascade={"persist","remove"},
     *                                                                                fetch="EXTRA_LAZY")
     */
    private $statHits;

    public function __construct()
    {
        $this->emailSentCount = 0;
        $this->created = new \DateTime();
    }

    public function getId(): int
    {
        return (int) $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getStarted(): \DateTime
    {
        return $this->started;
    }

    public function setStarted(\DateTime $started): self
    {
        $this->started = $started;

        return $this;
    }

    public function getEnded(): ?\DateTime
    {
        return $this->ended;
    }

    public function setEnded(\DateTime $ended): self
    {
        $this->ended = $ended;

        return $this;
    }

    public function getEmailSentCount(): int
    {
        return $this->emailSentCount;
    }

    public function setEmailSentCount(int $emailSentCount): self
    {
        $this->emailSentCount = $emailSentCount;

        return $this;
    }

    public function getMailing(): Mailing
    {
        return $this->mailing;
    }

    public function setMailing(Mailing $mailing): self
    {
        $this->mailing = $mailing;

        return $this;
    }

    public function getHtml(): string
    {
        return $this->html;
    }

    public function setHtml(string $html): self
    {
        $this->html = $html;

        return $this;
    }

    public function getStatHits(): array
    {
        return $this->statHits;
    }
}
