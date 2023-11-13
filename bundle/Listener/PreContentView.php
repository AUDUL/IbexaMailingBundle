<?php



declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Listener;

use CodeRhapsodie\IbexaMailingBundle\Entity\Mailing;
use CodeRhapsodie\IbexaMailingBundle\Security\Voter\Mailing as MailingVoter;
use Doctrine\ORM\EntityManagerInterface;
use Ibexa\Core\MVC\Symfony\Event\PreContentViewEvent;
use Ibexa\Core\MVC\Symfony\View\ContentView;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class PreContentView
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    public function __construct(
        EntityManagerInterface $entityManager,
        RequestStack $requestStack,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function onPreContentView(PreContentViewEvent $event): void
    {
        $contentView = $event->getContentView();
        if (!$contentView instanceof ContentView) {
            return;
        }

        if ('ibexamailingfull' !== $contentView->getViewType()) {
            return;
        }

        $masterRequest = $this->requestStack->getMasterRequest();
        if (null === $masterRequest) {
            return;
        }

        if (!$masterRequest->attributes->has('mailingId')) {
            return;
        }

        $mailing = $this->entityManager->getRepository(Mailing::class)->findOneById(
            (int) $masterRequest->attributes->get('mailingId')
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
