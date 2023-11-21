<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Repository;

use Doctrine\ORM\EntityRepository as BaseEntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * @template T of object
 *
 * @extends BaseEntityRepository<T>
 */
abstract class EntityRepository extends BaseEntityRepository
{
    /**
     * {@inheritDoc}
     *
     * @param array<string, mixed> $filters
     */
    public function createQueryBuilderForFilters(array $filters = []): QueryBuilder
    {
        return $this->createQueryBuilder($this->getAlias())->select($this->getAlias())->distinct();
    }

    /**
     * @param array<string, mixed> $filters
     *
     * @return array<T>
     */
    public function findByFilters(array $filters = []): array
    {
        $qb = $this->createQueryBuilderForFilters($filters);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function countByFilters(array $filters = []): int
    {
        $qb = $this->createQueryBuilderForFilters($filters);
        $qb->select($qb->expr()->countDistinct($this->getAlias().'.id'));

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    abstract protected function getAlias(): string;
}
