<?php



declare(strict_types=1);

namespace CodeRhapsodie\Bundle\IbexaMailingBundle\Entity\Compose;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

trait Metadata
{
    /**
     * @var DateTime
     * @ORM\Column(name="OBJ_created", type="datetime")
     */
    private $created;

    /**
     * @var DateTime
     * @ORM\Column(name="OBJ_updated", type="datetime")
     */
    private $updated;

    public function getCreated(): DateTime
    {
        return $this->created;
    }

    public function setCreated(DateTime $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getUpdated(): DateTime
    {
        return $this->updated;
    }

    public function setUpdated(DateTime $updated): self
    {
        $this->updated = $updated;

        return $this;
    }
}
