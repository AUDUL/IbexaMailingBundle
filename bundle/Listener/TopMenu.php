<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Listener;

use Ibexa\AdminUi\Menu\Event\ConfigureMenuEvent;
use Ibexa\AdminUi\Menu\MainMenuBuilder;
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
        $contentMenu = $menu->getChild(MainMenuBuilder::ITEM_CONTENT);
        $contentMenu->addChild(
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
    }

    public static function getSubscribedEvents()
    {
        return [
            ConfigureMenuEvent::MAIN_MENU => ['onMainMenuConfigure', 0],
        ];
    }
}
