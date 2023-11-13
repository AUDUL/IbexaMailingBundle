<?php



declare(strict_types=1);

namespace CodeRhapsodie\Bundle\IbexaMailingBundle\Entity\Compose;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait Names
{
    /**
     * @var array
     * @Assert\NotBlank()
     * @ORM\Column(name="OBJ_names", type="array", nullable=false)
     */
    private $names;

    /**
     * @return array
     */
    public function getNames(): ?array
    {
        return $this->names;
    }

    public function setNames(array $names): self
    {
        $this->names = $names;

        return $this;
    }

    public function getName(?string $lang = null): ?string
    {
        if (null === $this->names) {
            return null;
        }
        if (null === $lang || !isset($this->names[$lang])) {
            return array_values($this->names)[0];
        }

        return $this->names[$lang];
    }
}
