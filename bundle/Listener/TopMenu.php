<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Listener;

use Ibexa\AdminUi\Menu\Event\ConfigureMenuEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\RouterInterface;

class TopMenu implements EventSubscriberInterface
{
    public function __construct(private readonly RouterInterface $router)
    {
    }

    public function onMainMenuConfigure(ConfigureMenuEvent $event): void
    {
        $menu = $event->getMenu();
        $ibexaMailingMenu = $menu->addChild(
            'ibexamailing',
            [
                'route' => 'ibexamailing_dashboard_index',
                'label' => 'Ibexa Mailing',
                'extras' => [
                    'routes' => array_filter(array_keys($this->router->getRouteCollection()->all()), function (string $key) {
                        return str_starts_with($key, 'ibexamailing');
                    })
                ],
            ]
        );

        $ibexaMailingMenu->addChild(
            'ibexamailing_dashboard',
            [
                'route' => 'ibexamailing_dashboard_index',
                'label' => 'Dashboard',
            ]
        );

        $ibexaMailingMenu->addChild(
            'ibexamailing_mailing_list',
            [
                'route' => 'ibexamailing_mailinglist_index',
                'label' => 'Liste de diffusion',
                'extras' => [
                    'routes' => [
                        'ibexamailing_mailinglist_show',
                        'ibexamailing_mailinglist_index',
                        'ibexamailing_mailinglist_create',
                        'ibexamailing_mailinglist_remove',
                        'ibexamailing_mailinglist_import'
                    ]
                ],
            ]
        );
        $ibexaMailingMenu->addChild(
            'ibexamailing_user',
            [
                'route' => 'ibexamailing_user_index',
                'label' => 'Utilisateurs',
                'extras' => [
                    'routes' => [
                        'ibexamailing_user_remove',
                        'ibexamailing_user_show',
                    ]
                ],
            ]
        );
    }

    public static function getSubscribedEvents()
    {
        return [
            ConfigureMenuEvent::MAIN_MENU => ['onMainMenuConfigure', 0],
        ];
    }
}
