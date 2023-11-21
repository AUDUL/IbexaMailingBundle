<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Controller\Admin;

use CodeRhapsodie\IbexaMailingBundle\Core\Provider\User as UserProvider;
use CodeRhapsodie\IbexaMailingBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Ibexa\User\UserSetting\UserSettingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/show/{user}", name="ibexamailing_user_show")
     */
    public function showAction(User $user): Response
    {
        if ($user->isRestricted()) {
            throw new AccessDeniedHttpException('UserRepository has been restricted');
        }

        return $this->render('@IbexaMailing/admin/user/show.html.twig', [
            'item' => $user,
        ]);
    }

    /**
     * @Route("/delete/{user}", name="ibexamailing_user_remove")
     */
    public function deleteAction(
        User $user,
        EntityManagerInterface $entityManager,
        RouterInterface $router
    ): RedirectResponse {
        $entityManager->remove($user);
        $entityManager->flush();

        return new RedirectResponse($router->generate('ibexamailing_user_index'));
    }

    /**
     * @Route("/{status}/{page}", name="ibexamailing_user_index",
     *                                              defaults={"page":1, "status":"all"})
     */
    public function indexAction(UserProvider $provider, UserSettingService $userSettingService, string $status = 'all', int $page = 1): Response
    {
        $filters = [
            'status' => $status === 'all' ? null : $status,
        ];

        return $this->render('@IbexaMailing/admin/user/index.html.twig', [
            'pager' => $provider->getPagerFilters($filters, $page, (int) $userSettingService->getUserSetting('subitems_limit')->value),
            'statuses' => $provider->getStatusesData($filters),
            'currentStatus' => $status,
        ]);
    }
}
