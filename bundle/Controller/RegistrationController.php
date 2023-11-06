<?php

/**
 * NovaeZMailingBundle Bundle.
 *
 * @author    Novactive <s.morel@novactive.com>
 * @copyright 2018 Novactive
 * @license   https://github.com/Novactive/NovaeZMailingBundle/blob/master/LICENSE MIT Licence
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZMailingBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Novactive\Bundle\eZMailingBundle\Core\DataHandler\Registration;
use Novactive\Bundle\eZMailingBundle\Core\DataHandler\Unregistration;
use Novactive\Bundle\eZMailingBundle\Core\Registrar;
use Novactive\Bundle\eZMailingBundle\Entity\Campaign;
use Novactive\Bundle\eZMailingBundle\Entity\ConfirmationToken;
use Novactive\Bundle\eZMailingBundle\Entity\User;
use Novactive\Bundle\eZMailingBundle\Form\RegistrationType;
use Novactive\Bundle\eZMailingBundle\Security\Voter\Campaign as CampaignVoter;
use Novactive\Bundle\eZMailingBundle\Security\Voter\Mailing as MailingVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class RegistrationController
{

    public function __construct(
        private readonly Registrar                     $registrar,
        private readonly ConfigResolverInterface       $configResolver,
        private readonly EntityManagerInterface        $entityManager,
        private readonly AuthorizationCheckerInterface $authorizationChecker
    )
    {
    }

    /**
     * @Route("/register", name="novaezmailing_registration_create")
     *
     * @Template()
     */
    public function registerAction(Request $request, FormFactoryInterface $formFactory): array
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

        return $params;
    }

    /**
     * @Route("/register/default", name="novaezmailing_registration_default_create")
     *
     * @Template()
     */
    public function registerDefaultAction(Request $request, FormFactoryInterface $formFactory): array
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

        return $params;
    }

    /**
     * @Route("/register/confirm/{id}", name="novaezmailing_registration_confirm")
     *
     * @Template()
     */
    public function registerConfirmationAction(ConfirmationToken $token): array
    {
        return [
            'pagelayout' => $this->getPagelayout(),
            'title' => 'Confirm registration to Mailing Lists',
            'isConfirmed' => $this->registrar->confirm($token),
        ];
    }

    /**
     * @Route("/unregister/{email}", name="novaezmailing_registration_remove")
     *
     * @Template()
     */
    public function unregisterAction(string $email = null, Request $request, FormFactoryInterface $formFactory): array
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

        if ($this->configResolver->getParameter('unsubscribe_all', 'nova_ezmailing')) {
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

        $form = $formFactory->create(RegistrationType::class, $unregistration);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->registrar->askForUnregisterConfirmation($unregistration)) {
                return $params;
            }
        }

        $params += [
            'form' => $form->createView(),
            'unsubscribeAll' => $this->configResolver->getParameter('unsubscribe_all', 'nova_ezmailing'),
        ];

        return $params;
    }

    /**
     * @Route("/unregister/confirm/{id}", name="novaezmailing_unregistration_confirm")
     *
     * @Template()
     */
    public function unregisterConfirmationAction(ConfirmationToken $token): array
    {
        return [
            'pagelayout' => $this->getPagelayout(),
            'title' => 'Confirm unregistration to Mailing Lists',
            'isConfirmed' => $this->registrar->confirm($token),
        ];
    }

    private function getPagelayout(): string
    {
        return $this->configResolver->getParameter('pagelayout');
    }
}
