<?php



declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Repository;

class ConfirmationToken extends EntityRepository
{
    protected function getAlias(): string
    {
        return 'confirmtok';
    }
}
