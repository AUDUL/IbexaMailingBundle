<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Core\Modifier;

use CodeRhapsodie\IbexaMailingBundle\Entity\Mailing;
use CodeRhapsodie\IbexaMailingBundle\Entity\User;

interface ModifierInterface
{
    /**
     * @param array<string, mixed> $options
     */
    public function modify(Mailing $mailing, User $user, string $html, array $options = []): string;
}
