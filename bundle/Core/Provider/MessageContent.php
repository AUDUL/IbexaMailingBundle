<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Core\Provider;

use CodeRhapsodie\IbexaMailingBundle\Core\DataHandler\Registration;
use CodeRhapsodie\IbexaMailingBundle\Core\DataHandler\Unregistration;
use CodeRhapsodie\IbexaMailingBundle\Entity\Campaign;
use CodeRhapsodie\IbexaMailingBundle\Entity\ConfirmationToken;
use CodeRhapsodie\IbexaMailingBundle\Entity\Mailing;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;

class MessageContent
{
    public function __construct(
        private readonly ConfigResolverInterface $configResolver,
        private readonly TranslatorInterface $translator
    ) {
    }

    public function getStartSendingMailing(Mailing $mailing): TemplatedEmail
    {
        $translated = $this->translator->trans('messages.start_sending.being_sent3', [], 'ibexamailing');
        $message = $this->createMessage($translated, $mailing->getCampaign());
        $campaign = $mailing->getCampaign();
        $message->to($campaign->getReportEmail());

        $message->htmlTemplate('@IbexaMailing/messages/startsending.html.twig');
        $message->context(['item' => $mailing]);

        return $message;
    }

    public function getStopSendingMailing(Mailing $mailing): TemplatedEmail
    {
        $translated = $this->translator->trans('messages.stop_sending.sent3', [], 'ibexamailing');
        $message = $this->createMessage($translated, $mailing->getCampaign());
        $campaign = $mailing->getCampaign();
        $message->to($campaign->getReportEmail());

        $message->htmlTemplate('@IbexaMailing/messages/stopsending.html.twig');
        $message->context(['item' => $mailing]);

        return $message;
    }

    public function getRegistrationConfirmation(Registration $registration, ConfirmationToken $token): TemplatedEmail
    {
        $translated = $this->translator->trans('messages.confirm_registration.confirm', [], 'ibexamailing');
        $message = $this->createMessage($translated);
        $user = $registration->getUser();
        if ($user === null) {
            throw new \RuntimeException('UserRepository cannot be empty.');
        }
        $message->to($user->getEmail());
        $message->htmlTemplate('@IbexaMailing/messages/confirmregistration.html.twig');
        $message->context([
            'registration' => $registration,
            'token' => $token,
        ]);

        return $message;
    }

    public function getUnregistrationConfirmation(
        Unregistration $unregistration,
        ConfirmationToken $token
    ): TemplatedEmail {
        $translated = $this->translator->trans('messages.confirm_unregistration.confirmation', [], 'ibexamailing');
        $message = $this->createMessage($translated);
        $user = $unregistration->getUser();
        if ($user === null) {
            throw new \RuntimeException('UserRepository cannot be empty.');
        }
        $message->to($user->getEmail());
        $message->htmlTemplate('@IbexaMailing/messages/confirmunregistration.html.twig');
        $message->context([
            'unregistration' => $unregistration,
            'token' => $token,
        ]);

        return $message;
    }

    private function createMessage(string $subject, Campaign $campaign = null): TemplatedEmail
    {
        $prefix = $this->configResolver->getParameter('email_subject_prefix', 'ibexamailing');
        $message = new TemplatedEmail();
        $message->subject("{$prefix} {$subject}");
        if ($campaign !== null) {
            $message->from(new Address($campaign->getSenderEmail(), $campaign->getSenderName()));
            if (!empty($campaign->getReturnPathEmail())) {
                $message->returnPath($campaign->getReturnPathEmail());
            }

            return $message;
        }
        $message->from(
            new Address(
                $this->configResolver->getParameter('email_from_address', 'ibexamailing'),
                $this->configResolver->getParameter('email_from_name', 'ibexamailing')
            )
        );
        if (!empty($this->configResolver->getParameter('email_return_path', 'ibexamailing'))) {
            $message->returnPath($this->configResolver->getParameter('email_return_path', 'ibexamailing'));
        }

        return $message;
    }
}
