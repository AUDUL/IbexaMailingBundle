<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Repository;

/**
 * @extends EntityRepository<\CodeRhapsodie\IbexaMailingBundle\Entity\Broadcast>
 */
class BroadcastRepository extends EntityRepository
{
    /**
     * @return array<\CodeRhapsodie\IbexaMailingBundle\Entity\Broadcast>
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
