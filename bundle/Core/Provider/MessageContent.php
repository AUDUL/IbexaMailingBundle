<?php



declare(strict_types=1);

namespace CodeRhapsodie\Bundle\IbexaMailingBundle\Core\Provider;

use CodeRhapsodie\Bundle\IbexaMailingBundle\Core\DataHandler\Registration;
use CodeRhapsodie\Bundle\IbexaMailingBundle\Core\DataHandler\Unregistration;
use CodeRhapsodie\Bundle\IbexaMailingBundle\Entity\Campaign;
use CodeRhapsodie\Bundle\IbexaMailingBundle\Entity\ConfirmationToken;
use CodeRhapsodie\Bundle\IbexaMailingBundle\Entity\Mailing;
use Ibexa\Bundle\Core\DependencyInjection\Configuration\ConfigResolver;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
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
        $prefix = $this->configResolver->getParameter('email_subject_prefix', 'ibexamailing');
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
                $this->configResolver->getParameter('email_from_address', 'ibexamailing'),
                $this->configResolver->getParameter('email_from_name', 'ibexamailing')
            )
        );
        if (!empty($this->configResolver->getParameter('email_return_path', 'ibexamailing'))) {
            $message->returnPath($this->configResolver->getParameter('email_return_path', 'ibexamailing'));
        }
        return $message;
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
        if (null === $user) {
            throw new RuntimeException('User cannot be empty.');
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
        Unregistration    $unregistration,
        ConfirmationToken $token
    ): TemplatedEmail
    {
        $translated = $this->translator->trans('messages.confirm_unregistration.confirmation', [], 'ibexamailing');
        $message = $this->createMessage($translated);
        $user = $unregistration->getUser();
        if (null === $user) {
            throw new RuntimeException('User cannot be empty.');
        }
        $message->to($user->getEmail());
        $message->htmlTemplate('@IbexaMailing/messages/confirmunregistration.html.twig');
        $message->context([
            'unregistration' => $unregistration,
            'token' => $token,
        ]);

        return $message;
    }
}
