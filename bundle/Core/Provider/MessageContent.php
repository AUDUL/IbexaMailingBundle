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

namespace Novactive\Bundle\eZMailingBundle\Core\Provider;

use Ibexa\Bundle\Core\DependencyInjection\Configuration\ConfigResolver;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Novactive\Bundle\eZMailingBundle\Core\DataHandler\Registration;
use Novactive\Bundle\eZMailingBundle\Core\DataHandler\Unregistration;
use Novactive\Bundle\eZMailingBundle\Entity\Campaign;
use Novactive\Bundle\eZMailingBundle\Entity\ConfirmationToken;
use Novactive\Bundle\eZMailingBundle\Entity\Mailing;
use RuntimeException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;

class MessageContent
{
    /**
     * @var ConfigResolver
     */
    private $configResolver;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(
        ConfigResolverInterface $configResolver,
        TranslatorInterface     $translator
    )
    {
        $this->configResolver = $configResolver;
        $this->translator = $translator;
    }

    private function createMessage(string $subject, ?Campaign $campaign = null): TemplatedEmail
    {
        $prefix = $this->configResolver->getParameter('email_subject_prefix', 'nova_ezmailing');
        $message = new TemplatedEmail();
        $message->subject("{$prefix} {$subject}");
        if (null !== $campaign) {
            $message->from(new Address($campaign->getSenderEmail(), $campaign->getSenderName()));
            if (!empty($campaign->getReturnPathEmail())) {
                $message->returnPath($campaign->getReturnPathEmail());
            }

            return $message;
        }
        $message->from(
            new Address(
                $this->configResolver->getParameter('email_from_address', 'nova_ezmailing'),
                $this->configResolver->getParameter('email_from_name', 'nova_ezmailing')
            )
        );
        if (!empty($this->configResolver->getParameter('email_return_path', 'nova_ezmailing'))) {
            $message->returnPath($this->configResolver->getParameter('email_return_path', 'nova_ezmailing'));
        }
        return $message;
    }

    public function getStartSendingMailing(Mailing $mailing): TemplatedEmail
    {
        $translated = $this->translator->trans('messages.start_sending.being_sent3', [], 'ezmailing');
        $message = $this->createMessage($translated, $mailing->getCampaign());
        $campaign = $mailing->getCampaign();
        $message->to($campaign->getReportEmail());

        $message->htmlTemplate('@NovaeZMailing/messages/startsending.html.twig');
        $message->context(['item' => $mailing]);

        return $message;
    }

    public function getStopSendingMailing(Mailing $mailing): TemplatedEmail
    {
        $translated = $this->translator->trans('messages.stop_sending.sent3', [], 'ezmailing');
        $message = $this->createMessage($translated, $mailing->getCampaign());
        $campaign = $mailing->getCampaign();
        $message->to($campaign->getReportEmail());

        $message->htmlTemplate('@NovaeZMailing/messages/stopsending.html.twig');
        $message->context(['item' => $mailing]);

        return $message;
    }

    public function getRegistrationConfirmation(Registration $registration, ConfirmationToken $token): TemplatedEmail
    {
        $translated = $this->translator->trans('messages.confirm_registration.confirm', [], 'ezmailing');
        $message = $this->createMessage($translated);
        $user = $registration->getUser();
        if (null === $user) {
            throw new RuntimeException('User cannot be empty.');
        }
        $message->to($user->getEmail());
        $message->htmlTemplate('@NovaeZMailing/messages/confirmregistration.html.twig');
        $message->context([
            'registration' => $registration,
            'token' => $token,
        ]);

        return $message;
    }

    public function getUnregistrationConfirmation(
        Unregistration    $unregistration,
        ConfirmationToken $token
    ): TemplatedEmail
    {
        $translated = $this->translator->trans('messages.confirm_unregistration.confirmation', [], 'ezmailing');
        $message = $this->createMessage($translated);
        $user = $unregistration->getUser();
        if (null === $user) {
            throw new RuntimeException('User cannot be empty.');
        }
        $message->to($user->getEmail());
        $message->htmlTemplate('@NovaeZMailing/messages/confirmunregistration.html.twig');
        $message->context([
            'unregistration' => $unregistration,
            'token' => $token,
        ]);

        return $message;
    }
}
