<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="mailing_mailing_list")
 *
 * @ORM\Entity(repositoryClass="CodeRhapsodie\IbexaMailingBundle\Repository\MailingListRepository")
 */
class MailingList
{
    use Compose\Metadata;
    use Compose\Names;
    use Compose\Remote;

    /**
     * @var int
     *
     * @ORM\Column(name="ML_id", type="bigint", nullable=false)
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Registration[]
     *
     * @ORM\OrderBy({"created" = "ASC"})
     *
     * @ORM\OneToMany(targetEntity="RegistrationRepository", mappedBy="mailingList",
     *                                                                                      cascade={"persist","remove"},
     *                                                                                      orphanRemoval=true,
     *                                                                                      fetch="EXTRA_LAZY"
     * )
     */
    private $registrations;

    /**
     * @var array
     *
     * @ORM\Column(name="ML_siteaccess_limit", type="array", nullable=true)
     */
    private $siteaccessLimit;

    /**
     * @var bool
     *
     * @ORM\Column(name="ML_approbation", type="boolean", nullable=false)
     */
    private $withApproval;

    /**
     * @var Campaign[]
     *
     * @ORM\ManyToMany(targetEntity="CampaignRepository", mappedBy="mailingLists",
     *                                                                                  cascade={"persist"},
     *                                                                                  orphanRemoval=true,
     *                                                                                  fetch="EXTRA_LAZY")
     */
    private $campaigns;

    public function __construct()
    {
        $this->registrations = [];
        $this->created = new \DateTime();
        $this->withApproval = false;
    }

    public function __toString(): string
    {
        return $this->getName() ?? '';
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

    /**
     * @return Registration[]
     */
    public function getRegistrations(): array
    {
        return $this->registrations;
    }

    /**
     * @param Registration[] $registrations
     */
    public function setRegistrations(array $registrations): self
    {
        foreach ($registrations as $registration) {
            if (!$registration instanceof Registration) {
                throw new \RuntimeException(sprintf('Provided RegistrationRepository is not a %s', Registration::class));
            }
        }
        $this->registrations = $registrations;

        return $this;
    }

    public function isWithApproval(): bool
    {
        return $this->withApproval;
    }

    public function setWithApproval(bool $withApproval): self
    {
        $this->withApproval = $withApproval;

        return $this;
    }

    /**
     * @return array<mixed>|null
     */
    public function getSiteaccessLimit(): ?array
    {
        return $this->siteaccessLimit;
    }

    /**
     * @param array<mixed> $siteaccessLimit
     */
    public function setSiteaccessLimit(array $siteaccessLimit): self
    {
        $this->siteaccessLimit = $siteaccessLimit;

        return $this;
    }

    /**
     * @return Campaign[]
     */
    public function getCampaigns(): array
    {
        return $this->campaigns;
    }
}
