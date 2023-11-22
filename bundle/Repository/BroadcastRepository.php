<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Repository;

use CodeRhapsodie\IbexaMailingBundle\Entity\Broadcast;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends EntityRepository<Broadcast>
 */
class BroadcastRepository extends EntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Broadcast::class);
    }

    /**
     * @return array<Broadcast>
     */
    public function findLastBroadcasts(int $limit = 4): array
    {
        $qb = $this->createQueryBuilderForFilters();
        $qb->where("{$this->getAlias()}.emailSentCount > 0");
        $qb->setMaxResults($limit);
        $qb->orderBy("{$this->getAlias()}.ended", 'DESC');

        return $qb->getQuery()->getResult();
    }

    protected function getAlias(): string
    {
        return 'broadcast';
    }
}
