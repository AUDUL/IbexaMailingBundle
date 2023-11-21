<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Listener;

use CodeRhapsodie\IbexaMailingBundle\Entity\eZ\ContentInterface;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Mapping\PostLoad;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Repository;

/**
 * Class ContentLink
 * Link an eZ Content to an Entity.
 */
class EntityContentLink
{
    public function __construct(private readonly Repository $repository, private readonly LocationService $locationService)
    {
    }

    /**
     * @PostLoad
     *
     *  @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function postLoadHandler(ContentInterface $entity, PostLoadEventArgs $event): void
    {
        if ($entity->getLocationId() !== null) {
            try {
                $this->repository->sudo(function () use ($entity) {
                    $location = $this->locationService->loadLocation($entity->getLocationId());
                    $entity->setLocation($location);
                    $entity->setContent($location->getContent());
                });
            } catch (NotFoundException) {
            }
        }
    }
}
