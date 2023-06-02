<?php

/**
 * NovaeZMailingBundle Bundle.
 *
 * @package   Novactive\Bundle\eZMailingBundle
 *
 * @author    Novactive <s.morel@novactive.com>
 * @copyright 2018 Novactive
 * @license   https://github.com/Novactive/NovaeZMailingBundle/blob/master/LICENSE MIT Licence
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZMailingBundle\Listener;

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
            'eznovamailing',
            [
                'route' => 'novaezmailing_dashboard_index',
                'label' => 'Nova eZ Mailing',
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
