<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Core\Modifier;

use CodeRhapsodie\IbexaMailingBundle\Entity\Mailing;
use CodeRhapsodie\IbexaMailingBundle\Entity\User;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class Unregistration implements ModifierInterface
{
    public function __construct(private readonly RouterInterface $router)
    {
    }

    public function modify(Mailing $mailing, User $user, string $html, array $options = []): string
    {
        $url = $this->router->generate(
            'ibexamailing_registration_remove',
            [
                'email' => $user->getEmail(),
                'siteaccess' => $mailing->getSiteAccess(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $html = str_replace('##UNREGISTER_URL##', $url, $html);

        return $html;
    }
}
