<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Repository;

use Doctrine\ORM\QueryBuilder;

/**
 * @extends EntityRepository<\CodeRhapsodie\IbexaMailingBundle\Entity\Mailing>
 */
class MailingRepository extends EntityRepository
{
    /**
     * @param array<string, mixed> $filters
     */
    public function createQueryBuilderForFilters(
        array $filters = [],
        string $orderBy = null,
        int $limit = null
    ): QueryBuilder {
        $qb = parent::createQueryBuilderForFilters($filters);
        if (isset($filters['campaign'])) {
            $qb->andWhere($qb->expr()->eq('mail.campaign', ':campaign'))->setParameter(
                'campaign',
                $filters['campaign']
            );
        }

        if (isset($filters['status'])) {
            $qb->andWhere($qb->expr()->in('mail.status', ':statuses'))->setParameter('statuses', $filters['status']);
        }

        return $qb;
    }

    /**
     * @return array<\CodeRhapsodie\IbexaMailingBundle\Entity\Mailing>
     */
    public function findLastUpdated(int $limit = 10): array
    {
        $qb = $this->createQueryBuilderForFilters([]);
        $qb->setMaxResults($limit);
        $qb->orderBy("{$this->getAlias()}.updated", 'DESC');

        return $qb->getQuery()->getResult();
    }

    protected function getAlias(): string
    {
        return 'mail';
    }
}
