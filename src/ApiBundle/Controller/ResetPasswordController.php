<?php

namespace ApiBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\User;
use AppBundle\Entity\Profile;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class ResetPasswordController extends Controller
{
    /**
     * @Route("/api/reset-password", name="api.reset_password")
     */
    public function resetPasswordAction(Request $request)
    {
        $email = $request->get('email');
        $user = $em
            ->getRepository('AppBundle:User')
            ->findOneByEmail($formUser->getEmail())
        ;

        if ($user === nul) {
            return $this->json(
                [
                    'error' => [
                        'message' => $this->get('translator')->trans(
                            'api.reset_password.request.email_not_found.text'
                        ),
                    ],
                ],
                400
            );
        }

        $isPasswordCodeAlreadySent = $user->getResetPasswordCodeExpiresAt()
            && new \DateTime() < $user->getResetPasswordCodeExpiresAt();
        if ($isPasswordCodeAlreadySent) {
            return $this->json(
                [
                    'error' => [
                        'message' => $this->get('translator')->trans(
                            'api.reset_password.request.already_requested.text'
                        ),
                    ],
                ],
                400
            );
        }

        $this->get('app.user_manager')->resetPasswordRequest($user);

        return $this->json([
            'data' => [
                'message' => $this->get('translator')->trans(
                    'api.reset_password.request.success.text'
                ),
            ],
        ]);
    }
}
