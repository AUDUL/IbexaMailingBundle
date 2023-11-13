<?php



declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Repository;

class Campaign extends EntityRepository
{
    protected function getAlias(): string
    {
        return 'camp';
    }
}
