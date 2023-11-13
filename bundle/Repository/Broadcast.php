<?php



declare(strict_types=1);

namespace CodeRhapsodie\Bundle\IbexaMailingBundle\Repository;

class Broadcast extends EntityRepository
{
    protected function getAlias(): string
    {
        return 'broadcast';
    }

    public function findLastBroadcasts(int $limit = 4): array
    {
        $qb = $this->createQueryBuilderForFilters([]);
        $qb->where("{$this->getAlias()}.emailSentCount > 0");
        $qb->setMaxResults($limit);
        $qb->orderBy("{$this->getAlias()}.ended", 'DESC');

        return $qb->getQuery()->getResult();
    }
}
