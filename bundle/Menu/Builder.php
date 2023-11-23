<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Menu;

use CodeRhapsodie\IbexaMailingBundle\Entity\Campaign;
use CodeRhapsodie\IbexaMailingBundle\Entity\Mailing;
use CodeRhapsodie\IbexaMailingBundle\Repository\MailingRepository;
use CodeRhapsodie\IbexaMailingBundle\Repository\UserRepository;
use CodeRhapsodie\IbexaMailingBundle\Security\Voter\Campaign as CampaignVoter;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class Builder
{
    public function __construct(
        private readonly FactoryInterface $factory,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly TranslatorInterface $translator,
        private readonly UserRepository $userRepository,
        private readonly MailingRepository $mailingRepository
    ) {
    }

    public function createCampaignMenu(EntityManagerInterface $entityManager): ItemInterface
    {
        $menu = $this->factory->createItem('root');
        $repo = $entityManager->getRepository(Campaign::class);

        $campaigns = $repo->findAll();
        $mailingStatuses = Mailing::STATUSES;

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

            $count = $this->userRepository->countByFilters(['campaign' => $campaign]);

            $child->addChild(
                "camp_{$campaign->getId()}_subsciptions",
                [
                    'route' => 'ibexamailing_campaign_subscriptions',
                    'routeParameters' => ['campaign' => $campaign->getId()],
                    'label' => $this->translator->trans('menu.campaign_menu.subscriptions', [], 'ibexamailing')
                                         ." ({$count})",
                    'attributes' => [
                        'class' => 'leaf subscriptions',
                    ],
                ]
            );

            foreach ($mailingStatuses as $status) {
                $count = $this->mailingRepository->countByFilters(
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
