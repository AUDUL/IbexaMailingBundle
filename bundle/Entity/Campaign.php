<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
     * @var ArrayCollection<int, MailingList>
     *
     * @ORM\ManyToMany(targetEntity="CodeRhapsodie\IbexaMailingBundle\Entity\MailingList", inversedBy="campaigns")
     *
     * @ORM\JoinTable(name="mailing_campaign_mailinglists_destination",
     *      joinColumns={@ORM\JoinColumn(name="ML_id", referencedColumnName="CAMP_id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="CAMP_id", referencedColumnName="ML_id")}
     *      )
     *
     * @ORM\OrderBy({"created" = "ASC"})
     */
    private Collection $mailingLists;

    /**
     * @var ArrayCollection<int, Mailing>
     *
     * @ORM\OneToMany(targetEntity="CodeRhapsodie\IbexaMailingBundle\Entity\Mailing", mappedBy="campaign",
     *                                                                                cascade={"persist","remove"})
     */
    private Collection $mailings;

    public function __construct()
    {
        $this->mailingLists = new ArrayCollection();
        $this->mailings = new ArrayCollection();
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
     * @return ArrayCollection<int, MailingList>
     */
    public function getMailingLists(): Collection
    {
        return $this->mailingLists;
    }

    /**
     * @param array<MailingList> $mailingLists
     */
    public function setMailingLists(array $mailingLists): self
    {
        foreach ($mailingLists as $mailingList) {
            $this->addMailingList($mailingList);
        }

        return $this;
    }

    public function addMailingList(MailingList $mailingList): self
    {
        if ($this->mailingLists->contains($mailingList)) {
            return $this;
        }

        $this->mailingLists->add($mailingList);

        return $this;
    }

    /**
     * @return ArrayCollection<int, Mailing>
     */
    public function getMailings(): Collection
    {
        return $this->mailings;
    }

    /**
     * @param array<Mailing> $mailings
     */
    public function setMailings(array $mailings): self
    {
        foreach ($mailings as $mailing) {
            $this->addMailing($mailing);
        }

        return $this;
    }

    public function addMailing(Mailing $mailing): self
    {
        if ($this->mailings->contains($mailing)) {
            return $this;
        }

        $mailing->setCampaign($this);

        $this->mailings->add($mailing);

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
