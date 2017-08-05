<?php

namespace ApiBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Noxlogic\RateLimitBundle\Annotation\RateLimit;
use CoreBundle\Entity\User;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class ResetPasswordController extends Controller
{
    /**
     * @Route("/api/reset-password", name="api.reset_password")
     *
     * @RateLimit(limit=10, period=3600)
     */
    public function resetPasswordAction(Request $request)
    {
        $email = $request->get('email');
        if (empty($email)) {
            return $this->json(
                [
                    'error' => [
                        'message' => $this->get('translator')->trans(
                            'api.reset_password.request.email_empty.text'
                        ),
                    ],
                ],
                400
            );
        }

        $em = $this->getDoctrine()->getManager();
        $user = $em
            ->getRepository('CoreBundle:User')
            ->findOneByEmail($email)
        ;

        if ($user === null) {
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

        $isResetPasswordCodeExpired = $user->isResetPasswordCodeExpired();
        if ($isResetPasswordCodeExpired === false) {
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
