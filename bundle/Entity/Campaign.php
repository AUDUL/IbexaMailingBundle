<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A CampaignRepository contains generic information shared between the Mailings it contains.
 * It owns also a Link to a eZ Content that is going to be injected in the template.
 *
 * @ORM\Table(name="mailing_campaign")
 *
 * @ORM\Entity(repositoryClass="CodeRhapsodie\IbexaMailingBundle\Repository\CampaignRepository")
 *
 * @ORM\EntityListeners({"CodeRhapsodie\IbexaMailingBundle\Listener\EntityContentLink"})
 */
class Campaign implements eZ\ContentInterface
{
    use Compose\Metadata;
    use Compose\Names;
    use eZ\Content;

    /**
     * @var int
     *
     * @ORM\Column(name="CAMP_id", type="bigint", nullable=false)
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="CAMP_sender_name", type="string", length=255, nullable=false)
     */
    private $senderName;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     *
     * @Assert\Email()
     *
     * @ORM\Column(name="CAMP_sender_email", type="string", length=255, nullable=false)
     */
    private $senderEmail;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     *
     * @Assert\Email()
     *
     * @ORM\Column(name="CAMP_report_email", type="string", length=255, nullable=false)
     */
    private $reportEmail;

    /**
     * @var string|null
     *
     * @Assert\Email()
     *
     * @ORM\Column(name="CAMP_return_path_email", type="string", length=255, nullable=true)
     */
    private $returnPathEmail;

    /**
     * @var array
     *
     * @ORM\Column(name="CAMP_siteaccess_limit", type="array", nullable=true)
     */
    private $siteaccessLimit;

    /**
     * @var MailingList[]
     *
     * @ORM\ManyToMany(targetEntity="MailingListRepository", inversedBy="campaigns")
     *
     * @ORM\JoinTable(name="mailing_campaign_mailinglists_destination",
     *      joinColumns={@ORM\JoinColumn(name="ML_id", referencedColumnName="CAMP_id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="CAMP_id", referencedColumnName="ML_id")}
     *      )
     *
     * @ORM\OrderBy({"created" = "ASC"})
     */
    private $mailingLists;

    /**
     * @var Mailing[]
     *
     * @ORM\OneToMany(targetEntity="MailingRepository", mappedBy="campaign",
     *                                                                                cascade={"persist","remove"})
     */
    private $mailings;

    public function __construct()
    {
        $this->mailingLists = [];
        $this->mailings = [];
        $this->created = new \DateTime();
        $this->siteaccessLimit = [];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getSenderName(): ?string
    {
        return $this->senderName;
    }

    public function setSenderName(string $senderName): self
    {
        $this->senderName = $senderName;

        return $this;
    }

    public function getSenderEmail(): ?string
    {
        return $this->senderEmail;
    }

    public function setSenderEmail(string $senderEmail): self
    {
        $this->senderEmail = $senderEmail;

        return $this;
    }

    public function getReportEmail(): string
    {
        return $this->reportEmail;
    }

    public function setReportEmail(string $reportEmail): self
    {
        $this->reportEmail = $reportEmail;

        return $this;
    }

    public function getSiteaccessLimit(): ?array
    {
        return $this->siteaccessLimit;
    }

    public function setSiteaccessLimit(array $siteaccessLimit): self
    {
        $this->siteaccessLimit = $siteaccessLimit;

        return $this;
    }

    /**
     * @return MailingList[]
     */
    public function getMailingLists()
    {
        return $this->mailingLists;
    }

    public function setMailingLists(array $mailingLists): self
    {
        foreach ($mailingLists as $mailingList) {
            $this->addMailingList($mailingList);
        }

        return $this;
    }

    public function addMailingList(MailingList $mailingList): self
    {
        $contains = array_filter($this->mailingLists, function (MailingList $mailing) use ($mailingList) {
            return $mailing->getId() === $mailingList->getId();
        });

        if (!empty($contains)) {
            return $this;
        }

        $this->mailingLists[] = $mailingList;

        return $this;
    }

    /**
     * @return Mailing[]
     */
    public function getMailings(): array
    {
        return $this->mailings;
    }

    public function setMailings(array $mailings): self
    {
        foreach ($mailings as $mailing) {
            $this->addMailing($mailing);
        }

        return $this;
    }

    public function addMailing(Mailing $mailing): self
    {
        $contains = array_filter($this->mailings, function (Mailing $mail) use ($mailing) {
            return $mail->getId() === $mailing->getId();
        });
        if (!empty($contains)) {
            return $this;
        }

        $this->mailings[] = $mailing;

        $mailing->setCampaign($this);

        return $this;
    }

    public function getReturnPathEmail(): ?string
    {
        return $this->returnPathEmail;
    }

    public function setReturnPathEmail(?string $returnPathEmail): self
    {
        $this->returnPathEmail = $returnPathEmail;

        return $this;
    }
}
