<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Controller\Admin;

use CodeRhapsodie\IbexaMailingBundle\Core\Processor\TestMailingProcessorInterface as TestMailing;
use CodeRhapsodie\IbexaMailingBundle\Entity\Campaign;
use CodeRhapsodie\IbexaMailingBundle\Entity\Mailing;
use CodeRhapsodie\IbexaMailingBundle\Form\MailingType;
use CodeRhapsodie\IbexaMailingBundle\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Ibexa\AdminUi\Form\Factory\FormFactory;
use Ibexa\AdminUi\Tab\LocationView\ContentTab;
use Ibexa\AdminUi\UI\Module\Subitems\ContentViewParameterSupplier;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Core\Helper\TranslationHelper;
use Ibexa\Core\MVC\Symfony\SiteAccess\Router;
use Ibexa\Core\MVC\Symfony\View\ContentView;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\Registry;

/**
 * @Route("/mailing")
 */
class MailingController extends AbstractController
{
    /**
     * @Route("/show/{mailing}", name="ibexamailing_mailing_show")
     *
     * @IsGranted("view", subject="mailing")
     */
    public function showAction(
        Mailing $mailing,
        ContentViewParameterSupplier $contentViewParameterSupplier,
        FormFactory $formFactory
    ): Response {
        $contentView = new ContentView();
        $contentView->setLocation($mailing->getLocation());
        $contentView->setContent($mailing->getContent());
        $contentViewParameterSupplier->supply($contentView);

        $subitemsContentEdit = $formFactory->contentEdit(
            null,
            'form_subitems_content_edit'
        );

        return $this->render('@IbexaMailing/admin/mailing/show.html.twig', [
            'item' => $mailing,
            'form_subitems_content_edit' => $subitemsContentEdit->createView(),
            'subitems_module' => $contentView->getParameter('subitems_module'),
        ]);
    }

    /**
     * @IsGranted("view", subject="mailing")
     */
    public function mailingTabsAction(
        Mailing $mailing,
        Repository $repository,
        ContentTab $contentTab,
        UserRepository $userRepository
    ): Response {
        $content = $mailing->getContent();
        $contentType = $repository->getContentTypeService()->loadContentType(
            $content->contentInfo->contentTypeId
        );
        $preview = $contentTab->renderView(
            [
                'content' => $content,
                'location' => $mailing->getLocation(),
                'contentType' => $contentType,
            ]
        );

        return $this->render('@IbexaMailing/admin/mailing/mailing_tabs.html.twig', [
            'item' => $mailing,
            'totalRecipients' => $userRepository->countValidRecipients(
                $mailing->getCampaign()->getMailingLists()->toArray()
            ),
            'preview' => $preview,
        ]);
    }

    /**
     * @Route("/edit/{mailing}", name="ibexamailing_mailing_edit")
     *
     * @ParamConverter("mailing", class="CodeRhapsodie\IbexaMailingBundle\Entity\Mailing", options={"id"="mailing"})
     *
     * @Route("/create/{campaign}", name="ibexamailing_mailing_create")
     *
     * @ParamConverter("campaign", class="CodeRhapsodie\IbexaMailingBundle\Entity\Campaign", options={"id"="campaign"})
     *
     * @return Response
     */
    public function editAction(
        ?Mailing $mailing,
        ?Campaign $campaign,
        Request $request,
        FormFactoryInterface $formFactory,
        EntityManagerInterface $entityManager,
        Registry $workflows,
        TranslationHelper $translationHelper,
        Repository $repository
    ) {
        if ($mailing === null) {
            $mailing = new Mailing();
            $mailing
                ->setStatus(Mailing::DRAFT)
                ->setCampaign($campaign);
            $languages = array_filter($translationHelper->getAvailableLanguages());
            $mailing->setNames(array_combine($languages, array_pad([], \count($languages), '')));
        }

        $machine = $workflows->get($mailing);
        if (!$machine->can($mailing, 'edit')) {
            throw new AccessDeniedHttpException('Not Allowed');
        }

        $form = $formFactory->create(MailingType::class, $mailing);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $machine->apply($mailing, 'edit');
            $mailing->setUpdated(new \DateTime());
            $entityManager->persist($mailing);
            $entityManager->flush();

            return $this->redirectToRoute('ibexamailing_mailing_show', ['mailing' => $mailing->getId()]);
        }

        if ($mailing->getLocationId() !== null) {
            $location = $repository->getLocationService()->loadLocation($mailing->getLocationId());
            $content = $repository->getContentService()->loadContentByContentInfo($location->contentInfo);
            $mailing->setLocation($location);
            $mailing->setContent($content);
        }

        return $this->render('@IbexaMailing/admin/mailing/edit.html.twig', [
            'item' => $mailing,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/confirm/{mailing}", name="ibexamailing_mailing_confirm")
     * @Route("/archive/{mailing}", name="ibexamailing_mailing_archive")
     * @Route("/abort/{mailing}",   name="ibexamailing_mailing_cancel")
     *
     * @IsGranted("view", subject="mailing")
     */
    public function statusAction(
        Request $request,
        Mailing $mailing,
        EntityManagerInterface $entityManager,
        Registry $workflows
    ): RedirectResponse {
        $action = substr($request->get('_route'), \strlen('ibexamailing_mailing_'));
        $machine = $workflows->get($mailing);
        $machine->apply($mailing, $action);
        $entityManager->flush();

        return $this->redirectToRoute('ibexamailing_mailing_show', ['mailing' => $mailing->getId()]);
    }

    /**
     * @Route("/test/{mailing}", name="ibexamailing_mailing_test", methods={"POST"})
     *
     * @IsGranted("view", subject="mailing")
     */
    public function testAction(
        Request $request,
        Mailing $mailing,
        TestMailing $processor,
        EntityManagerInterface $entityManager,
        Registry $workflows,
        Router $ibexaRouter
    ): RedirectResponse {
        $machine = $workflows->get($mailing);
        $siteaccess = $ibexaRouter->getSiteAccess()->name;

        if ($machine->can($mailing, 'test')) {
            $ccEmail = $request->request->get('cc');
            if ($ccEmail !== '') {
                $processor->execute($mailing, $ccEmail);
                $machine->apply($mailing, 'test');
                $entityManager->flush();
            }
        }

        return $this->redirectToRoute('ibexamailing_mailing_show', ['mailing' => $mailing->getId(), 'siteaccess' => $siteaccess]);
    }
}
