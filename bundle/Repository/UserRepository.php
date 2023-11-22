<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Repository;

use CodeRhapsodie\IbexaMailingBundle\Entity\Campaign as CampaignEntity;
use CodeRhapsodie\IbexaMailingBundle\Entity\MailingList;
use CodeRhapsodie\IbexaMailingBundle\Entity\User;
use CodeRhapsodie\IbexaMailingBundle\Entity\User as UserEntity;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends EntityRepository<UserEntity>
 */
class UserRepository extends EntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function createQueryBuilderForFilters(array $filters = []): QueryBuilder
    {
        $qb = parent::createQueryBuilderForFilters($filters);
        $qb->where($qb->expr()->eq('u.restricted', ':restricted'))->setParameter('restricted', false);

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
            $joinExpr = $qb->expr()->andX(
                $qb->expr()->in('reg.mailingList', ':mailingLists')
            );
            if (isset($filters['isApproved'])) {
                $joinExpr->add($qb->expr()->eq('reg.approved', ':approved'));
            }
            $qb
                ->innerJoin(
                    'u.registrations',
                    'reg',
                    Join::WITH,
                    $joinExpr
                )->setParameter('mailingLists', $mailingLists);

            if (isset($filters['isApproved'])) {
                $qb->setParameter('approved', $filters['isApproved']);
            }
        }

        if (isset($filters['status'])) {
            $qb->andWhere($qb->expr()->in('u.status', ':statuses'))->setParameter('statuses', $filters['status']);
        }

        if (isset($filters['query'])) {
            $query = $filters['query'];
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('u.email', ':query'),
                    $qb->expr()->like('u.firstName', ':query'),
                    $qb->expr()->like('u.lastName', ':query')
                )
            )->setParameter('query', '%'.$query.'%');
        }

        $qb->orderBy('u.created', 'DESC');

        return $qb;
    }

    /**
     * @param MailingList[] $mailingLists
     *
     * @return array<UserEntity>
     */
    public function findValidRecipients(array $mailingLists): array
    {
        return $this->findByFilters(
            [
                'mailingLists' => $mailingLists,
                'isApproved' => true,
                'status' => [UserEntity::CONFIRMED, UserEntity::SOFT_BOUNCE],
            ]
        );
    }

    /**
     * @param MailingList[] $mailingLists
     */
    public function countValidRecipients(array $mailingLists): int
    {
        return $this->countByFilters(
            [
                'mailingLists' => $mailingLists,
                'isApproved' => true,
                'status' => [UserEntity::CONFIRMED, UserEntity::SOFT_BOUNCE],
            ]
        );
    }

    /**
     * @return array<UserEntity>
     */
    public function findLastUpdated(int $limit = 10): array
    {
        $qb = $this->createQueryBuilderForFilters([]);
        $qb->setMaxResults($limit);
        $qb->orderBy("{$this->getAlias()}.updated", 'DESC');

        return $qb->getQuery()->getResult();
    }

    public function existByEmail(string $email): bool
    {
        $qb = $this->createQueryBuilder('u');

        return $qb->select('count(u.email)')
                ->where($qb->expr()->eq('u.email', ':email'))
                ->setParameter('email', $email)
                ->getQuery()
                ->getSingleScalarResult() > 0;
    }

    protected function getAlias(): string
    {
        return 'u';
    }
}
