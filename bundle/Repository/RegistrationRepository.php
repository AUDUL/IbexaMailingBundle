<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Repository;

use CodeRhapsodie\IbexaMailingBundle\Entity\Campaign as CampaignEntity;
use CodeRhapsodie\IbexaMailingBundle\Entity\Registration;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends EntityRepository<Registration>
 */
class RegistrationRepository extends EntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Registration::class);
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function createQueryBuilderForFilters(array $filters = []): QueryBuilder
    {
        $qb = parent::createQueryBuilderForFilters($filters);
        $qb
            ->innerJoin('reg.user', 'user', Join::WITH, $qb->expr()->eq('user.restricted', ':restricted'))
            ->setParameter('restricted', false);

        $mailingLists = null;
        if (isset($filters['campaign'])) {
            /** @var CampaignEntity $campaign */
            $campaign = $filters['campaign'];
            $mailingLists = $campaign->getMailingLists();
        }
        if (isset($filters['mailingLists'])) {
            $mailingLists = $filters['mailingLists'];
        }
        if ($mailingLists !== null) {
            $qb->andWhere($qb->expr()->in('reg.mailingList', ':mailinglists'))->setParameter(
                'mailinglists',
                $mailingLists
            );
        }
        if (isset($filters['isApproved'])) {
            $qb->andWhere($qb->expr()->in('reg.approved', ':approved'))->setParameter(
                'approved',
                $filters['isApproved']
            );
        }

        return $qb;
    }

    protected function getAlias(): string
    {
        return 'reg';
    }
}
