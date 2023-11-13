<?php



declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Repository;

use Doctrine\ORM\QueryBuilder;

class MailingList extends EntityRepository
{
    protected function getAlias(): string
    {
        return 'ml';
    }

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
}
