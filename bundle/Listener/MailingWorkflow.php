<?php



declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Listener;

use Psr\Log\LoggerInterface;
use Symfony\Component\Workflow\Event\Event;

class MailingWorkflow
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onWorkflowMailingLeave(Event $event): void
    {
        $this->logger->notice(
            sprintf(
                'Mailing %s (id: "%s") performed transaction "%s" from "%s" to "%s"',
                $event->getSubject()->getName(),
                $event->getSubject()->getId(),
                $event->getTransition()->getName(),
                implode(', ', array_keys($event->getMarking()->getPlaces())),
                implode(', ', $event->getTransition()->getTos())
            )
        );
    }
}
