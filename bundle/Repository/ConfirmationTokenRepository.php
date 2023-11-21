<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Repository;

/**
 * @extends EntityRepository<\CodeRhapsodie\IbexaMailingBundle\Entity\ConfirmationToken>
 */
class ConfirmationTokenRepository extends EntityRepository
{
    protected function getAlias(): string
    {
        return 'confirmtok';
    }
}
