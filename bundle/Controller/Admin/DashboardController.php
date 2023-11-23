<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Controller\Admin;

use CodeRhapsodie\IbexaMailingBundle\Entity\MailingList;
use CodeRhapsodie\IbexaMailingBundle\Entity\User;
use CodeRhapsodie\IbexaMailingBundle\Repository\BroadcastRepository;
use CodeRhapsodie\IbexaMailingBundle\Repository\MailingListRepository;
use CodeRhapsodie\IbexaMailingBundle\Repository\MailingRepository;
use CodeRhapsodie\IbexaMailingBundle\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    /**
     * @Route("/", name="ibexamailing_dashboard_index")
     */
    public function indexAction(BroadcastRepository $broadcastRepository, UserRepository $userRepository, MailingRepository $mailingRepository): Response
    {
        return $this->render('@IbexaMailing/admin/dashboard/index.html.twig', [
            'broadcasts' => $broadcastRepository->findLastBroadcasts(5),
            'mailings' => $mailingRepository->findLastUpdated(5),
            'users' => $userRepository->findLastUpdated(5),
        ]);
    }

    /**
     * @Route("/search/autocomplete", name="ibexamailing_dashboard_search_autocomplete")
     */
    public function autocompleteSearchAction(
        Request $request,
        MailingListRepository $mailingListRepository,
        UserRepository $userRepository
    ): JsonResponse {
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse('Not Authorized', 403);
        }

        $query = $request->query->get('query');

        $users = $userRepository->findByFilters(['query' => $query]);

        $userResults = array_map(
            function (User $user) {
                $userName = trim($user->getFirstName().' '.$user->getLastName());
                if ($userName === '') {
                    $userName = $user->getEmail();
                }

                return [
                    'value' => $userName,
                    'data' => $this->generateUrl('ibexamailing_user_show', ['user' => $user->getId()]),
                ];
            },
            $users
        );

        $mailingLists = $mailingListRepository->findByFilters(['query' => $query]);
        $mailingListResults = array_map(
            function (MailingList $mailingList) {
                return [
                    'value' => trim($mailingList->getName()),
                    'data' => $this->generateUrl(
                        'ibexamailing_mailinglist_show',
                        ['mailingList' => $mailingList->getId()]
                    ),
                ];
            },
            $mailingLists
        );

        return new JsonResponse(['suggestions' => $userResults + $mailingListResults]);
    }
}
