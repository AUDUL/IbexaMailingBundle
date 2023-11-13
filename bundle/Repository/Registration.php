<?php



declare(strict_types=1);

namespace CodeRhapsodie\Bundle\IbexaMailingBundle\Repository;

use CodeRhapsodie\Bundle\IbexaMailingBundle\Entity\Campaign as CampaignEntity;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

class Registration extends EntityRepository
{
    protected function getAlias(): string
    {
        return 'reg';
    }

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
        if (null !== $mailingLists) {
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
}
