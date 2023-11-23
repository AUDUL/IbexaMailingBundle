<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Entity\Compose;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait Names
{
    /**
     * @var array<mixed>
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="OBJ_names", type="array", nullable=false)
     */
    private array $names;

    /**
     * @return array<mixed>|null
     */
    public function getNames(): ?array
    {
        return $this->names;
    }

    /**
     * @param array<mixed> $names
     */
    public function setNames(array $names): self
    {
        $this->names = $names;

        return $this;
    }

    public function getName(string $lang = null): ?string
    {
        if ($lang === null || !isset($this->names[$lang])) {
            return array_values($this->names)[0];
        }

        return $this->names[$lang];
    }
}
