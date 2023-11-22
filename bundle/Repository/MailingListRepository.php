<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Repository;

use CodeRhapsodie\IbexaMailingBundle\Entity\MailingList;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends EntityRepository<MailingList>
 */
class MailingListRepository extends EntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MailingList::class);
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function createQueryBuilderForFilters(array $filters = []): QueryBuilder
    {
        $qb = parent::createQueryBuilderForFilters($filters);
        if (isset($filters['query'])) {
            $query = $filters['query'];
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('ml.names', ':query')
                )
            )->setParameter('query', '%'.$query.'%');
        }

        return $qb;
    }

    protected function getAlias(): string
    {
        return 'ml';
    }
}
