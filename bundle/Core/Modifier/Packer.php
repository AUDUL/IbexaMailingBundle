<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Core\Modifier;

use CodeRhapsodie\IbexaMailingBundle\Entity\Mailing;
use CodeRhapsodie\IbexaMailingBundle\Entity\User;

class Packer implements ModifierInterface
{
    public function modify(Mailing $mailing, User $user, string $html, array $options = []): string
    {
        return str_replace(["\n", "\r"], ['', ''], $html);
    }
}
