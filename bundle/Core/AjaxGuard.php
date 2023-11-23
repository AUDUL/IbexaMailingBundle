<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Core;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Proxy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class AjaxGuard
{
    public function __construct(private readonly CsrfTokenManagerInterface $csrfTokenManager, private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * @return array<string, CsrfToken>
     */
    public function execute(Request $request, mixed $subject, callable $callback): array
    {
        $token = $request->request->get('token');
        if (
            !$request->isXmlHttpRequest() || $token === null
            || !$this->isEntity($subject)
            || !method_exists($subject, 'getId')
            || !$this->csrfTokenManager->isTokenValid(new CsrfToken((string) $subject->getId(), $token))
        ) {
            throw new AccessDeniedHttpException('Not Allowed');
        }
        $results = $callback($subject);
        $this->entityManager->persist($subject);
        $this->entityManager->flush();

        return ['token' => $this->csrfTokenManager->getToken((string) $subject->getId())->getValue()] + $results;
    }

    private function isEntity(mixed $class): bool
    {
        if (\is_object($class)) {
            $class = ($class instanceof Proxy)
                ? get_parent_class($class)
                : $class::class;
        }

        return !$this->entityManager->getMetadataFactory()->isTransient($class);
    }
}
