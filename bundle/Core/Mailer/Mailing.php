<?php



declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Core\Mailer;

use CodeRhapsodie\IbexaMailingBundle\Core\Provider\Broadcast;
use CodeRhapsodie\IbexaMailingBundle\Core\Provider\MailingContent;
use CodeRhapsodie\IbexaMailingBundle\Entity\Mailing as MailingEntity;
use CodeRhapsodie\IbexaMailingBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Message;

/**
 * Class Mailing.
 */
class Mailing
{
    public function __construct(
        private readonly Simple                 $simpleMailer,
        private readonly MailingContent         $contentProvider,
        private readonly LoggerInterface        $logger,
        private readonly Broadcast              $broadcastProvider,
        private readonly EntityManagerInterface $entityManager,
        private readonly MailerInterface        $mailer,
        private readonly string                 $mailing
    )
    {
    }

    public function sendMailing(MailingEntity $mailing, string $forceRecipient = null): void
    {
        $nativeHtml = $this->contentProvider->preFetchContent($mailing);
        $broadcast = $this->broadcastProvider->start($mailing, $nativeHtml);

        $this->simpleMailer->sendStartSendingMailingMessage($mailing);
        if ($forceRecipient) {
            $fakeUser = new User();
            $fakeUser->setEmail($forceRecipient);
            $fakeUser->setFirstName('XXXX');
            $fakeUser->setLastName('YYYY');
            $contentMessage = $this->contentProvider->getContentMailing($mailing, $fakeUser, $broadcast);
            $this->logger->debug("Mailing Mailer starts to test {$contentMessage->getSubject()}.");
            $this->sendMessage($contentMessage);
        } else {
            $campaign = $mailing->getCampaign();
            $this->logger->notice("Mailing Mailer starts to send Mailing {$mailing->getName()}");
            $recipientCounts = 0;
            $userRepo = $this->entityManager->getRepository(User::class);
            $recipients = $userRepo->findValidRecipients($campaign->getMailingLists());
            foreach ($recipients as $user) {
                /** @var User $user */
                $contentMessage = $this->contentProvider->getContentMailing($mailing, $user, $broadcast);
                $this->sendMessage($contentMessage);
                ++$recipientCounts;

                if (0 === $recipientCounts % 10) {
                    $broadcast->setEmailSentCount($recipientCounts);
                    $this->broadcastProvider->store($broadcast);
                }
            }

            //send copy of email
            $fakeUser = new User();
            $fakeUser->setEmail($mailing->getCampaign()->getReportEmail());
            $fakeUser->setFirstName('XXXX');
            $fakeUser->setLastName('YYYY');
            $contentMessage = $this->contentProvider->getContentMailing($mailing, $fakeUser, $broadcast);
            $this->sendMessage($contentMessage);

            $this->broadcastProvider->store($broadcast);
            $this->logger->notice("Mailing {$mailing->getName()} induced {$recipientCounts} emails sent.");
        }
        $this->simpleMailer->sendStopSendingMailingMessage($mailing);
        $this->broadcastProvider->end($broadcast);
    }

    private function sendMessage(Message $message): void
    {
        $message->getHeaders()->addTextHeader('X-Transport', $this->mailing);
        $this->mailer->send($message);
    }
}
