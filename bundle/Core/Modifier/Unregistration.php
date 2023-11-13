<?php



declare(strict_types=1);

namespace CodeRhapsodie\Bundle\IbexaMailingBundle\Core\Modifier;

use CodeRhapsodie\Bundle\IbexaMailingBundle\Entity\Mailing;
use CodeRhapsodie\Bundle\IbexaMailingBundle\Entity\User;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class Unregistration
{
    /**
     * @var
     */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
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
