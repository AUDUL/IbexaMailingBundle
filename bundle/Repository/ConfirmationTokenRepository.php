<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Repository;

use CodeRhapsodie\IbexaMailingBundle\Entity\ConfirmationToken;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends EntityRepository<ConfirmationToken>
 */
class ConfirmationTokenRepository extends EntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConfirmationToken::class);
    }

    protected function getAlias(): string
    {
        return 'confirmtok';
    }
}
