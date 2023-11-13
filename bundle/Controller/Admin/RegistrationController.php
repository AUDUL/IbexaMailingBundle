<?php



declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Controller\Admin;

use CodeRhapsodie\IbexaMailingBundle\Core\AjaxGuard;
use CodeRhapsodie\IbexaMailingBundle\Entity\Registration;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/registration")
 */
class RegistrationController
{
    /**
     * @Route("/accept/{registration}", name="ibexamailing_registration_accept", methods={"POST"})
     */
    public function acceptAction(
        Request $request,
        AjaxGuard $ajaxGuard,
        Registration $registration
    ): JsonResponse {
        $token = $ajaxGuard->execute(
            $request,
            $registration,
            function (Registration $registration) {
                $registration->setApproved(true);

                return [];
            }
        );

        return new JsonResponse(['token' => $token]);
    }

    /**
     * @Route("/deny/{registration}", name="ibexamailing_registration_deny")
     */
    public function denyAction(
        Request $request,
        AjaxGuard $ajaxGuard,
        Registration $registration
    ): JsonResponse {
        $results = $ajaxGuard->execute(
            $request,
            $registration,
            function (Registration $registration) {
                $registration->setApproved(false);

                return [];
            }
        );

        return new JsonResponse($results);
    }
}
