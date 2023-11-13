<?php



declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Core\Modifier;

use CodeRhapsodie\IbexaMailingBundle\Entity\Mailing;
use CodeRhapsodie\IbexaMailingBundle\Entity\User;

interface ModifierInterface
{
    public function modify(Mailing $mailing, User $user, string $html, array $options = []): string;
}
