<?php



declare(strict_types=1);

namespace CodeRhapsodie\Bundle\IbexaMailingBundle\Core\Modifier;

use CodeRhapsodie\Bundle\IbexaMailingBundle\Entity\Mailing;
use CodeRhapsodie\Bundle\IbexaMailingBundle\Entity\User;

interface ModifierInterface
{
    public function modify(Mailing $mailing, User $user, string $html, array $options = []): string;
}
