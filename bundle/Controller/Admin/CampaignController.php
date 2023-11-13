<?php



declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Controller\Admin;

use CodeRhapsodie\IbexaMailingBundle\Core\Provider\User as UserProvider;
use CodeRhapsodie\IbexaMailingBundle\Entity\Campaign;
use CodeRhapsodie\IbexaMailingBundle\Entity\Mailing;
use CodeRhapsodie\IbexaMailingBundle\Form\CampaignType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Ibexa\AdminUi\Tab\LocationView\ContentTab;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Core\Helper\TranslationHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

/**
 * @Route("/campaign")
 */
class CampaignController
{
    /**
     * @Template()
     * @Security("is_granted('view', campaign)")
     */
    public function campaignTabsAction(
        Campaign $campaign,
        Repository $repository,
        ContentTab $contentTab,
        EntityManagerInterface $entityManager,
        string $status = 'all'
    ): array {
        $content = $campaign->getContent();
        if (null !== $content) {
            $contentType = $repository->getContentTypeService()->loadContentType(
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
        $repo = $entityManager->getRepository(Mailing::class);
        $mailings = $repo->findByFilters(
            [
                'campaign' => $campaign,
                'status' => 'all' === $status ? null : $status,
            ]
        );

        return [
            'item' => $campaign,
            'status' => $status,
            'children' => $mailings,
            'preview' => $preview ?? null,
        ];
    }

    /**
     * @Route("/show/subscriptions/{campaign}/{status}/{page}/{limit}", name="ibexamailing_campaign_subscriptions",
     *                                              defaults={"page":1, "limit":10, "status":"all"})
     * @Template()
     * @Security("is_granted('view', campaign)")
     */
    public function subscriptionsAction(
        Campaign $campaign,
        UserProvider $provider,
        string $status = 'all',
        int $page = 1,
        int $limit = 10
    ): array {
        $filers = [
            'campaign' => $campaign,
            'status' => 'all' === $status ? null : $status,
        ];

        return [
            'pager' => $provider->getPagerFilters($filers, $page, $limit),
            'statuses' => $provider->getStatusesData($filers),
            'currentStatus' => $status,
            'item' => $campaign,
        ];
    }

    /**
     * @Route("/show/mailings/{campaign}/{status}", name="ibexamailing_campaign_mailings")
     * @Template()
     * @Security("is_granted('view', campaign)")
     */
    public function mailingsAction(Campaign $campaign, EntityManagerInterface $entityManager, string $status): array
    {
        $repo = $entityManager->getRepository(Mailing::class);
        $results = $repo->findByFilters(
            [
                'campaign' => $campaign,
                'status' => $status,
            ]
        );

        return [
            'item' => $campaign,
            'status' => $status,
            'children' => $results,
        ];
    }

    /**
     * @Route("/edit/{campaign}", name="ibexamailing_campaign_edit")
     * @Route("/create", name="ibexamailing_campaign_create")
     * @Security("is_granted('edit', campaign)")
     * @Template()
     *
     * @return array|RedirectResponse
     */
    public function editAction(
        ?Campaign $campaign,
        Request $request,
        RouterInterface $router,
        EntityManagerInterface $entityManager,
        FormFactoryInterface $formFactory,
        TranslationHelper $translationHelper
    ) {
        if (null === $campaign) {
            $campaign = new Campaign();
            $languages = array_filter($translationHelper->getAvailableLanguages());
            $campaign->setNames(array_combine($languages, array_pad([], count($languages), '')));
        }

        $form = $formFactory->create(CampaignType::class, $campaign);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $campaign->setUpdated(new DateTime());
            $entityManager->persist($campaign);
            $entityManager->flush();

            return new RedirectResponse(
                $router->generate('ibexamailing_campaign_subscriptions', ['campaign' => $campaign->getId()])
            );
        }

        return [
            'item' => $campaign,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/delete/{campaign}", name="ibexamailing_campaign_remove")
     * @Security("is_granted('edit', campaign)")
     */
    public function deleteAction(
        Campaign $campaign,
        EntityManagerInterface $entityManager,
        RouterInterface $router
    ): RedirectResponse {
        $entityManager->remove($campaign);
        $entityManager->flush();

        return new RedirectResponse($router->generate('ibexamailing_dashboard_index'));
    }
}
