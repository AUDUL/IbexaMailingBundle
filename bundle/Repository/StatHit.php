<?php



declare(strict_types=1);

namespace CodeRhapsodie\Bundle\IbexaMailingBundle\Repository;

use CodeRhapsodie\Bundle\IbexaMailingBundle\Entity\Broadcast as BroadcastEntity;
use Doctrine\ORM\QueryBuilder;

class StatHit extends EntityRepository
{
    protected function getAlias(): string
    {
        return 'stathit';
    }

    public function createQueryBuilderForFilters(array $filters = []): QueryBuilder
    {
        $qb = parent::createQueryBuilderForFilters($filters);
        if (isset($filters['broadcasts'])) {
            $broadcasts = $filters['broadcasts'];
        }
        if (null !== $broadcasts) {
            $qb->andWhere($qb->expr()->in('stathit.broadcast', ':broadcasts'))->setParameter(
                'broadcasts',
                $broadcasts
            );
        }

        return $qb;
    }

    /**
     * @param BroadcastEntity[] $broadcasts
     */
    public function getBrowserMapCount($broadcasts): array
    {
        $qb = $this->createQueryBuilderForFilters(['broadcasts' => $broadcasts]);
        $qb->select($qb->expr()->count($this->getAlias().'.id').' as nb', $this->getAlias().'.browserName');
        $qb->groupBy($this->getAlias().'.userKey', $this->getAlias().'.browserName');
        $results = $qb->getQuery()->getArrayResult();
        $mappedResults = [];

        foreach ($results as $result) {
            $mappedResults[$result['browserName']] = (int) $result['nb'];
        }

        return $mappedResults;
    }

    /**
     * @param BroadcastEntity[] $broadcasts
     */
    public function getOSMapCount($broadcasts): array
    {
        $qb = $this->createQueryBuilderForFilters(['broadcasts' => $broadcasts]);
        $qb->select($qb->expr()->count($this->getAlias().'.id').' as nb', $this->getAlias().'.osName');
        $qb->groupBy($this->getAlias().'.userKey', $this->getAlias().'.osName');
        $results = $qb->getQuery()->getArrayResult();
        $mappedResults = [];

        foreach ($results as $result) {
            $mappedResults[$result['osName']] = (int) $result['nb'];
        }

        return $mappedResults;
    }

    /**
     * @param BroadcastEntity[] $broadcasts
     */
    public function getURLMapCount($broadcasts): array
    {
        $qb = $this->createQueryBuilderForFilters(['broadcasts' => $broadcasts]);
        $qb->select($qb->expr()->count($this->getAlias().'.id').' as nb', $this->getAlias().'.url');
        $qb->andWhere($qb->expr()->notIn($this->getAlias().'.url', ':url'))->setParameter('url', '-');
        $qb->groupBy($this->getAlias().'.userKey', $this->getAlias().'.url');
        $results = $qb->getQuery()->getArrayResult();
        $mappedResults = [];

        foreach ($results as $result) {
            $mappedResults[$result['url']] = (int) $result['nb'];
        }

        return $mappedResults;
    }

    /**
     * @param BroadcastEntity[] $broadcasts
     */
    public function getOpenedCount($broadcasts): int
    {
        $qb = $this->createQueryBuilderForFilters(['broadcasts' => $broadcasts]);
        $qb->select($qb->expr()->countDistinct($this->getAlias().'.userKey').' as nb');
        $qb->andWhere($qb->expr()->eq($this->getAlias().'.url', ':url'))->setParameter('url', '-');

        return (int) ($qb->getQuery()->getOneOrNullResult()['nb'] ?? 0);
    }

    /**
     * @param BroadcastEntity[] $broadcasts
     */
    public function getOpenedCountPerDay($broadcasts): array
    {
        $qb = $this->createQueryBuilderForFilters(['broadcasts' => $broadcasts]);
        $qb->select(
            $qb->expr()->countDistinct($this->getAlias().'.userKey').' as nb',
            'SUBSTRING(stathit.created, 1, 10) as day'
        );
        $qb->andWhere($qb->expr()->eq($this->getAlias().'.url', ':url'))->setParameter('url', '-');
        $qb->groupBy('day');
        $qb->orderBy('day', 'DESC');
        $results = $qb->getQuery()->getArrayResult();
        $mappedResults = [];

        foreach ($results as $result) {
            $mappedResults[$result['day']] = (int) $result['nb'];
        }

        return $mappedResults;
    }
}
