<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Repository;

/**
 * @extends EntityRepository<\CodeRhapsodie\IbexaMailingBundle\Entity\Campaign>
 */
class CampaignRepository extends EntityRepository
{
    protected function getAlias(): string
    {
        return 'camp';
    }
}
