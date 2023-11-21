<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Entity\eZ;

use Ibexa\Contracts\Core\Repository\Values\Content\Content as eZContent;
use Ibexa\Contracts\Core\Repository\Values\Content\Location as eZLocation;

interface ContentInterface
{
    public function getLocationId(): ?int;

    public function setLocationId(int $locationId): self;

    public function getContent(): ?eZContent;

    public function setContent(eZContent $content): self;

    public function getLocation(): ?eZLocation;

    public function setLocation(eZLocation $location): self;
}
