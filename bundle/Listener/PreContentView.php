<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Listener;

use CodeRhapsodie\IbexaMailingBundle\Entity\Mailing;
use CodeRhapsodie\IbexaMailingBundle\Repository\MailingRepository;
use CodeRhapsodie\IbexaMailingBundle\Security\Voter\Mailing as MailingVoter;
use Ibexa\Core\MVC\Symfony\Event\PreContentViewEvent;
use Ibexa\Core\MVC\Symfony\View\ContentView;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class PreContentView
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly MailingRepository $mailingRepository
    ) {
    }

    public function onPreContentView(PreContentViewEvent $event): void
    {
        $contentView = $event->getContentView();
        if (!$contentView instanceof ContentView) {
            return;
        }

        if ($contentView->getViewType() !== 'ibexamailingfull') {
            return;
        }

        $masterRequest = $this->requestStack->getMainRequest();
        if ($masterRequest === null) {
            return;
        }

        if (!$masterRequest->attributes->has('mailingId')) {
            return;
        }

        $mailing = $this->mailingRepository->findOneBy(
            [
                'id' => (int) $masterRequest->attributes->get('mailingId'),
            ]
        );

        if (!$mailing instanceof Mailing) {
            return;
        }
        if (!$this->authorizationChecker->isGranted(MailingVoter::VIEW, $mailing)) {
            return;
        }

        $contentView->addParameters(['mailing' => $mailing]);
    }
}
