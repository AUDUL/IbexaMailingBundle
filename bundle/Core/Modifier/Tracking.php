<?php



declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Core\Modifier;

use CodeRhapsodie\IbexaMailingBundle\Entity\Broadcast;
use CodeRhapsodie\IbexaMailingBundle\Entity\Mailing;
use CodeRhapsodie\IbexaMailingBundle\Entity\User;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class Tracking implements ModifierInterface
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
        /** @var Broadcast $broadcast */
        $broadcast = $options['broadcast'];
        $uniqId = uniqid('ibexamailing-', true);
        $readUrl = $this->router->generate(
            'ibexamailing_t_read',
            [
                'salt' => $uniqId,
                'broadcastId' => $broadcast->getId(),
                'siteaccess' => $mailing->getSiteAccess(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $readMarker = "<img src=\"{$readUrl}\" width=\"1\" height=\"1\" />";

        $html = str_replace('</body>', "{$readMarker}</body>", $html);

        return preg_replace_callback(
            '/<a(.[^>]*)href="http(s)?(.[^"]*)"/uimx',
            function ($aInput) use ($uniqId, $broadcast, $mailing) {
                $continueUrl = $this->router->generate(
                    'ibexamailing_t_continue',
                    [
                        'salt' => str_replace('.', '', $uniqId),
                        'broadcastId' => $broadcast->getId(),
                        'url' => str_replace(['+', '/'], ['-', '_'], base64_encode('http' . trim($aInput[1]) . trim($aInput[2]) . trim($aInput[3]))),
                        'siteaccess' => $mailing->getSiteAccess(),
                    ],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                return "<a{$aInput[1]}href=\"{$continueUrl}\"";
            },
            $html
        );
    }
}
