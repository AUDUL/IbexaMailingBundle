<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Controller;

use CodeRhapsodie\IbexaMailingBundle\Core\DataHandler\Registration;
use CodeRhapsodie\IbexaMailingBundle\Core\DataHandler\Unregistration;
use CodeRhapsodie\IbexaMailingBundle\Core\Registrar;
use CodeRhapsodie\IbexaMailingBundle\Entity\Campaign;
use CodeRhapsodie\IbexaMailingBundle\Entity\ConfirmationToken;
use CodeRhapsodie\IbexaMailingBundle\Entity\User;
use CodeRhapsodie\IbexaMailingBundle\Form\RegistrationType;
use CodeRhapsodie\IbexaMailingBundle\Security\Voter\Campaign as CampaignVoter;
use CodeRhapsodie\IbexaMailingBundle\Security\Voter\Mailing as MailingVoter;
use Doctrine\ORM\EntityManagerInterface;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class RegistrationController extends AbstractController
{
    public function __construct(
        private readonly Registrar                     $registrar,
        private readonly ConfigResolverInterface       $configResolver,
        private readonly EntityManagerInterface        $entityManager,
        private readonly AuthorizationCheckerInterface $authorizationChecker
    ) {
    }

    /**
     * @Route("/register", name="ibexamailing_registration_create")
     *
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function registerAction(Request $request, FormFactoryInterface $formFactory): Response
    {
        $params = [
            'pagelayout' => $this->getPagelayout(),
            'title' => 'Register to Mailing Lists',
        ];

        $registration = new Registration();

        $form = $formFactory->create(RegistrationType::class, $registration);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->registrar->askForConfirmation($registration);
        } else {
            $params += [
                'form' => $form->createView(),
            ];
        }

        return $this->render('@IbexaMailing/registration/register.html.twig', $params);
    }

    /**
     * @Route("/register/default", name="ibexamailing_registration_default_create")
     *
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function registerDefaultAction(Request $request, FormFactoryInterface $formFactory): Response
    {
        $params = [
            'pagelayout' => $this->getPagelayout(),
            'title' => 'Register to Default Mailing List',
        ];

        $registration = new Registration();

        $form = $formFactory->create(RegistrationType::class, $registration);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->registrar->askForConfirmation($registration);
        } else {
            if ($form->isSubmitted() === false) {
                $form->get('mailingLists')->setData($this->registrar->getDefaultMailingList());
            }

            $params += [
                'form' => $form->createView(),
            ];
        }

        return $this->render('@IbexaMailing/registration/register_default.html.twig', $params);
    }

    /**
     * @Route("/register/confirm/{id}", name="ibexamailing_registration_confirm")
     */
    public function registerConfirmationAction(ConfirmationToken $token): Response
    {
        return $this->render('@IbexaMailing/registration/register_confirmation.html.twig', [
            'pagelayout' => $this->getPagelayout(),
            'title' => 'Confirm registration to Mailing Lists',
            'isConfirmed' => $this->registrar->confirm($token),
        ]);
    }

    /**
     * @Route("/unregister/{email}", name="ibexamailing_registration_remove")
     */
    public function unregisterAction(string $email = null, Request $request, FormFactoryInterface $formFactory): Response
    {
        $params = [
            'pagelayout' => $this->getPagelayout(),
            'title' => 'Unregister to Mailing Lists',
        ];

        $unregistration = new Unregistration();

        if ($email !== null) {
            $user = new User();
            $user
                ->setEmail($email)
                ->setUpdated(new \DateTime());
            $unregistration->setUser($user);
        }

        if ($this->configResolver->getParameter('unsubscribe_all', 'ibexamailing')) {
            $this->unsubscribeAll($unregistration);
        }

        $form = $formFactory->create(RegistrationType::class, $unregistration);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->registrar->askForUnregisterConfirmation($unregistration)) {
                $params['isConfirmed'] = true;
                return $this->render('@IbexaMailing/registration/unregister_confirmation.html.twig', $params);
            }
        }

        $params += [
            'form' => $form->createView(),
            'unsubscribeAll' => $this->configResolver->getParameter('unsubscribe_all', 'ibexamailing'),
        ];

        return $this->render('@IbexaMailing/registration/unregister.html.twig', $params);
    }

    /**
     * @Route("/unregister/confirm/{id}", name="ibexamailing_unregistration_confirm")
     */
    public function unregisterConfirmationAction(ConfirmationToken $token): Response
    {
        return $this->render('@IbexaMailing/registration/unregister_confirmation.html.twig', [
            'pagelayout' => $this->getPagelayout(),
            'title' => 'Confirm unregistration to Mailing Lists',
            'isConfirmed' => $this->registrar->confirm($token),
        ]);
    }

    private function getPagelayout(): string
    {
        return $this->configResolver->getParameter('pagelayout');
    }

    private function unsubscribeAll(Unregistration $unregistration): void
    {
        $allowedMailingList = [];
        $campaignRepository = $this->entityManager->getRepository(Campaign::class);
        // permissions on Campaing can be more complex, then we don't filter in SQL
        foreach ($campaignRepository->findAll() as $campaign) {
            if ($this->authorizationChecker->isGranted(CampaignVoter::VIEW, $campaign)) {
                foreach ($campaign->getMailingLists() as $mailingList) {
                    if ($this->authorizationChecker->isGranted(MailingVoter::VIEW, $mailingList)) {
                        $allowedMailingList[] = $mailingList;
                    }
                }
            }
        }

        $unregistration->setMailingLists($allowedMailingList);
    }
}
