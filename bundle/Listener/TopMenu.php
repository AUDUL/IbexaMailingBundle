<?php



declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Listener;

use Ibexa\AdminUi\Menu\Event\ConfigureMenuEvent;
use Ibexa\AdminUi\Menu\MainMenuBuilder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TopMenu implements EventSubscriberInterface
{
    public function onMainMenuConfigure(ConfigureMenuEvent $event): void
    {
        $menu = $event->getMenu();
        $contentMenu = $menu->getChild(MainMenuBuilder::ITEM_CONTENT);
        $contentMenu->addChild(
            'ibexamailing',
            [
                'route' => 'ibexamailing_dashboard_index',
                'label' => 'Ibexa Mailing',
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
