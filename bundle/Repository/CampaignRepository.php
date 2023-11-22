<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Repository;

use CodeRhapsodie\IbexaMailingBundle\Entity\Campaign;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends EntityRepository<Campaign>
 */
class CampaignRepository extends EntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Campaign::class);
    }

    protected function getAlias(): string
    {
        return 'camp';
    }
}
