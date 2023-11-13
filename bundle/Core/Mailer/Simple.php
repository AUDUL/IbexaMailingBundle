<?php



declare(strict_types=1);

namespace CodeRhapsodie\Bundle\IbexaMailingBundle\Core\Mailer;

use CodeRhapsodie\Bundle\IbexaMailingBundle\Core\DataHandler\Registration;
use CodeRhapsodie\Bundle\IbexaMailingBundle\Core\DataHandler\Unregistration;
use CodeRhapsodie\Bundle\IbexaMailingBundle\Core\Provider\MessageContent;
use CodeRhapsodie\Bundle\IbexaMailingBundle\Entity\ConfirmationToken;
use CodeRhapsodie\Bundle\IbexaMailingBundle\Entity\Mailing as MailingEntity;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Message;

class Simple
{
    public function __construct(private readonly MessageContent  $messageProvider,
                                private readonly LoggerInterface $logger,
                                private readonly MailerInterface $mailer,
                                private readonly string          $simpleMailer
    )
    {
    }

    public function sendStartSendingMailingMessage(MailingEntity $mailing): void
    {
        $message = $this->messageProvider->getStartSendingMailing($mailing);
        $this->sendMessage($message);
    }

    public function sendStopSendingMailingMessage(MailingEntity $mailing): void
    {
        $message = $this->messageProvider->getStopSendingMailing($mailing);
        $this->sendMessage($message);
    }

    public function sendRegistrationConfirmation(Registration $registration, ConfirmationToken $token): void
    {
        $message = $this->messageProvider->getRegistrationConfirmation($registration, $token);
        $this->sendMessage($message);
    }

    public function sendUnregistrationConfirmation(Unregistration $unregistration, ConfirmationToken $token): void
    {
        $message = $this->messageProvider->getUnregistrationConfirmation($unregistration, $token);
        $this->sendMessage($message);
    }

    private function sendMessage(Message $message): void
    {
        $this->logger->debug("Simple Mailer sends {$message->getSubject()}.");
        $message->getHeaders()->addTextHeader('X-Transport', $this->simpleMailer);

        $this->mailer->send($message);
    }
}
