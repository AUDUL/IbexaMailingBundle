<?php



declare(strict_types=1);

namespace CodeRhapsodie\Bundle\IbexaMailingBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="mailing_confirmation_token")
 *
 * @ORM\Entity(repositoryClass="CodeRhapsodie\Bundle\IbexaMailingBundle\Repository\ConfirmationToken")
 */
class ConfirmationToken
{
    use Compose\Metadata;

    public const REGISTER = 'register';

    public const UNREGISTER = 'unregister';

    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(name="CT_id", type="guid", unique=true)
     */
    private $id;

    /**
     * @var array
     * @ORM\Column(name="CT_payload", type="array", nullable=false)
     */
    private $payload;

    public function __construct()
    {
        $this->created = new DateTime();
        $this->updated = new DateTime();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function setPayload(array $payload): self
    {
        $this->payload = $payload;

        return $this;
    }
}
