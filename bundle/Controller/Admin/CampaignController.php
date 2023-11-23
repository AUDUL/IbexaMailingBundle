<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Controller\Admin;

use CodeRhapsodie\IbexaMailingBundle\Core\Provider\User as UserProvider;
use CodeRhapsodie\IbexaMailingBundle\Entity\Campaign;
use CodeRhapsodie\IbexaMailingBundle\Form\CampaignType;
use CodeRhapsodie\IbexaMailingBundle\Repository\MailingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Ibexa\AdminUi\Tab\LocationView\ContentTab;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Core\Helper\TranslationHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/campaign")
 */
class CampaignController extends AbstractController
{
    /**
     * @Security("is_granted('view', campaign)")
     */
    public function campaignTabsAction(
        Campaign $campaign,
        ContentTypeService $contentTypeService,
        ContentTab $contentTab,
        MailingRepository $mailingRepository,
        string $status = 'all',
    ): Response {
        $content = $campaign->getContent();
        if ($content !== null) {
            $contentType = $contentTypeService->loadContentType(
                $content->contentInfo->contentTypeId
            );
            $preview = $contentTab->renderView(
                [
                    'content' => $content,
                    'location' => $campaign->getLocation(),
                    'contentType' => $contentType,
                ]
            );
        }
        $mailings = $mailingRepository->findByFilters(
            [
                'campaign' => $campaign,
                'status' => $status === 'all' ? null : $status,
            ]
        );

        return $this->render('@IbexaMailing/admin/campaign/campaign_tabs.html.twig', [
            'item' => $campaign,
            'status' => $status,
            'children' => $mailings,
            'preview' => $preview ?? null,
        ]);
    }

    /**
     * @Route("/show/subscriptions/{campaign}/{status}/{page}/{limit}", name="ibexamailing_campaign_subscriptions",
     *                                              defaults={"page":1, "limit":10, "status":"all"})
     *
     * @Security("is_granted('view', campaign)")
     */
    public function subscriptionsAction(
        Campaign $campaign,
        UserProvider $provider,
        string $status = 'all',
        int $page = 1,
        int $limit = 10
    ): Response {
        $filers = [
            'campaign' => $campaign,
            'status' => $status === 'all' ? null : $status,
        ];

        return $this->render('@IbexaMailing/admin/campaign/subscriptions.html.twig', [
            'pager' => $provider->getPagerFilters($filers, $page, $limit),
            'statuses' => $provider->getStatusesData($filers),
            'currentStatus' => $status,
            'item' => $campaign,
        ]);
    }

    /**
     * @Route("/show/mailings/{campaign}/{status}", name="ibexamailing_campaign_mailings")
     *
     * @Security("is_granted('view', campaign)")
     */
    public function mailingsAction(Campaign $campaign, MailingRepository $mailingRepository, string $status): Response
    {
        $results = $mailingRepository->findByFilters(
            [
                'campaign' => $campaign,
                'status' => $status,
            ]
        );

        return $this->render('@IbexaMailing/admin/campaign/mailings.html.twig', [
            'item' => $campaign,
            'status' => $status,
            'children' => $results,
        ]);
    }

    /**
     * @Route("/edit/{campaign}", name="ibexamailing_campaign_edit")
     * @Route("/create", name="ibexamailing_campaign_create")
     *
     * @Security("is_granted('edit', campaign)")
     */
    public function editAction(
        ?Campaign $campaign,
        Request $request,
        EntityManagerInterface $entityManager,
        FormFactoryInterface $formFactory,
        TranslationHelper $translationHelper
    ): Response {
        if ($campaign === null) {
            $campaign = new Campaign();
            $languages = array_filter($translationHelper->getAvailableLanguages());
            $campaign->setNames(array_combine($languages, array_pad([], \count($languages), '')));
        }

        $form = $formFactory->create(CampaignType::class, $campaign);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $campaign->setUpdated(new \DateTime());
            $entityManager->persist($campaign);
            $entityManager->flush();

            return $this->redirectToRoute('ibexamailing_campaign_subscriptions', ['campaign' => $campaign->getId()]);
        }

        return $this->render('@IbexaMailing/admin/campaign/edit.html.twig', [
            'item' => $campaign,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/delete/{campaign}", name="ibexamailing_campaign_remove")
     *
     * @Security("is_granted('edit', campaign)")
     */
    public function deleteAction(
        Campaign $campaign,
        EntityManagerInterface $entityManager,
    ): RedirectResponse {
        $entityManager->remove($campaign);
        $entityManager->flush();

        return $this->redirectToRoute('ibexamailing_dashboard_index');
    }
}
