<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Controller;

use CodeRhapsodie\IbexaMailingBundle\Core\Utils\Browser;
use CodeRhapsodie\IbexaMailingBundle\Entity\StatHit;
use CodeRhapsodie\IbexaMailingBundle\Repository\BroadcastRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/t")
 */
class TrackController
{
    public const PIXEL_CONTENT = 'R0lGODlhAQABAJAAAP8AAAAAACH5BAUQAAAALAAAAAABAAEAAAICBAEAOw==';
    public const PIXEL_CONTENT_TYPE = 'image/gif';

    /**
     * @Route("/continue/{salt}/{broadcastId}/{url}", name="ibexamailing_t_continue")
     */
    public function continueAction(
        string $salt,
        int $broadcastId,
        string $url,
        EntityManagerInterface $entityManager,
        BroadcastRepository $broadcastRepository,
        Request $request
    ): RedirectResponse {
        $broadcast = $broadcastRepository->find($broadcastId);
        $browser = new Browser($request->headers->get('user-agent', 'Unknown'));
        $stat = new StatHit();
        $decodedUrl = base64_decode(str_replace(['-', '_'], ['+', '/'], $url));
        $stat
            ->setOsName($browser->getPlatform())
            ->setBrowserName($browser->getName())
            ->setUserKey($salt)
            ->setUrl($decodedUrl)
            ->setBroadcast($broadcast)
            ->setUpdated(new \DateTime());
        $entityManager->persist($stat);
        $entityManager->flush();

        return new RedirectResponse($decodedUrl);
    }

    /**
     * @Route("/read/{salt}/{broadcastId}", name="ibexamailing_t_read")
     */
    public function readAction(
        string $salt,
        int $broadcastId,
        EntityManagerInterface $entityManager,
        Request $request,
        BroadcastRepository $broadcastRepository
    ): Response {
        $broadcast = $broadcastRepository->find($broadcastId);
        $browser = new Browser($request->headers->get('user-agent', 'Unknown'));
        $stat = new StatHit();
        $stat
            ->setOsName($browser->getPlatform())
            ->setBrowserName($browser->getName())
            ->setUserKey($salt)
            ->setUrl('-')
            ->setBroadcast($broadcast)
            ->setUpdated(new \DateTime());
        $entityManager->persist($stat);
        $entityManager->flush();

        $response = new Response(base64_decode(self::PIXEL_CONTENT));
        $response->headers->set('Content-Type', self::PIXEL_CONTENT_TYPE);
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);

        return $response;
    }
}
