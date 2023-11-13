<?php



declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Listener;

use CodeRhapsodie\IbexaMailingBundle\Entity\eZ\ContentInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping\PostLoad;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Repository;

/**
 * Class ContentLink
 * Link an eZ Content to an Entity.
 */
class EntityContentLink
{
    /**
     * @var Repository
     */
    private $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /** @PostLoad */
    public function postLoadHandler(ContentInterface $entity, LifecycleEventArgs $event): void
    {
        if (null !== $entity->getLocationId()) {
            try {
                $this->repository->sudo(function () use ($entity) {
                    $location = $this->repository->getLocationService()->loadLocation($entity->getLocationId());
                    $content = $this->repository->getContentService()->loadContentByContentInfo($location->contentInfo);
                    $entity->setLocation($location);
                    $entity->setContent($content);
                });
            } catch (NotFoundException) {
            }
        }
    }
}
