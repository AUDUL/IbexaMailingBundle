<?php



declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Controller\Admin;

use CodeRhapsodie\IbexaMailingBundle\Core\Provider\User as UserProvider;
use CodeRhapsodie\IbexaMailingBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Ibexa\User\UserSetting\UserSettingService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

/**
 * @Route("/user")
 */
class UserController
{
    /**
     * @Route("/show/{user}", name="ibexamailing_user_show")
     * @Template()
     */
    public function showAction(User $user): array
    {
        if ($user->isRestricted()) {
            throw new AccessDeniedHttpException('User has been restricted');
        }

        return [
            'item' => $user,
        ];
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
     * @Template()
     */
    public function indexAction(UserProvider $provider, UserSettingService $userSettingService, string $status = 'all', int $page = 1): array
    {
        $filters = [
            'status' => 'all' === $status ? null : $status,
        ];

        return [
            'pager' => $provider->getPagerFilters($filters, $page, (int)$userSettingService->getUserSetting('subitems_limit')->value),
            'statuses' => $provider->getStatusesData($filters),
            'currentStatus' => $status,
        ];
    }
}
