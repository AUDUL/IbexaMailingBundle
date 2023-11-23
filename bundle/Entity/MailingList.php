<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
     * @var ArrayCollection<int, Registration>
     *
     * @ORM\OrderBy({"created" = "ASC"})
     *
     * @ORM\OneToMany(targetEntity="CodeRhapsodie\IbexaMailingBundle\Entity\Registration", mappedBy="mailingList",
     *                                                                                      cascade={"persist","remove"},
     *                                                                                      orphanRemoval=true,
     *                                                                                      fetch="EXTRA_LAZY"
     * )
     */
    private Collection $registrations;

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
     * @var ArrayCollection<int, Campaign>
     *
     * @ORM\ManyToMany(targetEntity="CodeRhapsodie\IbexaMailingBundle\Entity\Campaign", mappedBy="mailingLists",
     *                                                                                  cascade={"persist"},
     *                                                                                  orphanRemoval=true,
     *                                                                                  fetch="EXTRA_LAZY")
     */
    private Collection $campaigns;

    public function __construct()
    {
        $this->registrations = new ArrayCollection();
        $this->campaigns = new ArrayCollection();
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
     * @return ArrayCollection<int, Registration>
     */
    public function getRegistrations(): Collection
    {
        return $this->registrations;
    }

    /**
     * @param Collection<int, Registration> $registrations
     */
    public function setRegistrations(Collection $registrations): self
    {
        foreach ($registrations->toArray() as $registration) {
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
     * @return ArrayCollection<int, Campaign>
     */
    public function getCampaigns(): Collection
    {
        return $this->campaigns;
    }
}
