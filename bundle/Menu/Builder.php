<?php



declare(strict_types=1);

namespace CodeRhapsodie\Bundle\IbexaMailingBundle\Menu;

use CodeRhapsodie\Bundle\IbexaMailingBundle\Entity\Campaign;
use CodeRhapsodie\Bundle\IbexaMailingBundle\Entity\Mailing;
use CodeRhapsodie\Bundle\IbexaMailingBundle\Entity\User;
use CodeRhapsodie\Bundle\IbexaMailingBundle\Security\Voter\Campaign as CampaignVoter;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class Builder
{
    private $translator;

    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    public function __construct(
        FactoryInterface $factory,
        AuthorizationCheckerInterface $authorizationChecker,
        TranslatorInterface $translator
    ) {
        $this->factory = $factory;
        $this->authorizationChecker = $authorizationChecker;
        $this->translator = $translator;
    }

    public function createAdminMenu(RequestStack $requestStack): ItemInterface
    {
        $request = $requestStack->getMainRequest();
        $route = $request?->attributes->get('_route');
        $mailingRoute = 'ibexamailing_mailinglist';
        $userRoute = 'ibexamailing_user';

        $menu = $this->factory->createItem('root');
        $child = $menu->addChild(
            'mailinglists',
            [
                'route' => "{$mailingRoute}_index",
                'label' => $this->translator->trans('menu.admin_menu.mailing_lists', [], 'ibexamailing'),
            ]
        );

        if (substr($route, 0, \strlen($mailingRoute)) === $mailingRoute) {
            $child->setCurrent(true);
        }

        $child = $menu->addChild(
            'users',
            [
                'route' => "{$userRoute}_index",
                'label' => $this->translator->trans('menu.admin_menu.users', [], 'ibexamailing'),
            ]
        );
        if (substr($route, 0, \strlen($userRoute)) === $userRoute) {
            $child->setCurrent(true);
        }

        return $menu;
    }

    public function createCampaignMenu(RequestStack $requestStack, EntityManagerInterface $entityManager): ItemInterface
    {
        $menu = $this->factory->createItem('root');
        $repo = $entityManager->getRepository(Campaign::class);

        $campaigns = $repo->findAll();
        $mailingStatuses = Mailing::STATUSES;

        $userRepo = $entityManager->getRepository(User::class);
        $mailingRepo = $entityManager->getRepository(Mailing::class);
        foreach ($campaigns as $campaign) {
            if (!$this->authorizationChecker->isGranted(CampaignVoter::VIEW, $campaign)) {
                continue;
            }
            $child = $menu->addChild(
                "camp_{$campaign->getId()}",
                [
                    'label' => $campaign->getName(),
                ]
            );

            $count = $userRepo->countByFilters(['campaign' => $campaign]);

            $child->addChild(
                "camp_{$campaign->getId()}_subsciptions",
                [
                    'route' => 'ibexamailing_campaign_subscriptions',
                    'routeParameters' => ['campaign' => $campaign->getId()],
                    'label' => $this->translator->trans('menu.campaign_menu.subscriptions', [], 'ibexamailing') .
                                         " ({$count})",
                    'attributes' => [
                        'class' => 'leaf subscriptions',
                    ],
                ]
            );

            foreach ($mailingStatuses as $status) {
                $count = $mailingRepo->countByFilters(
                    [
                        'campaign' => $campaign,
                        'status' => $status,
                    ]
                );
                $child->addChild(
                    "mailing_status_{$status}",
                    [
                        'route' => 'ibexamailing_campaign_mailings',
                        'routeParameters' => [
                            'campaign' => $campaign->getId(),
                            'status' => $status,
                        ],
                        'label' => $this->translator->trans(
                            'generic.mailing_statuses.'.$status,
                            [],
                                'ibexamailing'
                        )." ({$count})",
                        'attributes' => [
                            'class' => "leaf {$status}",
                        ],
                    ]
                );
            }
        }

        return $menu;
    }

    public function createSaveCancelMenu(): ItemInterface
    {
        $menu = $this->factory->createItem('root');
        $menu->addChild(
            'ibexamailing_save',
            [
                'label' => $this->translator->trans('menu.savecancel.save', [], 'ibexamailing'),
                'extras' => [
                    'icon' => 'save',
                ],
            ]
        );

        $menu->addChild(
            'ibexamailing_cancel',
            [
                'label' => $this->translator->trans('menu.savecancel.cancel', [], 'ibexamailing'),
                'attributes' => ['class' => 'btn-danger'],
                'extras' => [
                    'icon' => 'circle-close',
                ],
            ]
        );

        return $menu;
    }
}
