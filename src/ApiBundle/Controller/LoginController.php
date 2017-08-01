<?php

namespace ApiBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Noxlogic\RateLimitBundle\Annotation\RateLimit;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class LoginController extends Controller
{
    /**
     * @Route("/api/login", name="api.login")
     *
     * @RateLimit(limit=10, period=3600)
     */
    public function loginAction(Request $request)
    {
        $username = $request->get('username');
        $password = $request->get('password');

        if (
            empty($username) ||
            empty($password)
        ) {
            return $this->json(
                [
                    'error' => [
                        'message' => $this->get('translator')->trans(
                            'api.login.username_or_password_empty.text'
                        ),
                    ],
                ],
                400
            );
        }

        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('AppBundle:User')
            ->findByUsernameOrEmail($username);

        if (empty($user)) {
            $this->container->get('app.user_action_manager')
                ->add(
                    'user.api.login.fail',
                    $this->translator->trans(
                        'api.login.fail.user_action.text'
                    ),
                    [
                        'username' => $username,
                    ]
                );

            return $this->json(
                [
                    'error' => [
                        'message' => $this->get('translator')->trans(
                            'api.login.user_not_found.text'
                        ),
                    ],
                ],
                400
            );
        }

        $encoder = $this->get('security.password_encoder');
        $encodedPassword = $encoder->encodePassword(
            $password,
            $user->getSalt()
        );

        if ($encodedPassword !== $user->getPassword()) {
            $this->container->get('app.user_action_manager')
                ->add(
                    'user.api.login.fail',
                    $this->translator->trans(
                        'api.login.fail.user_action.text'
                    ),
                    [
                        'username' => $username,
                    ],
                    $user
                );

            return $this->json(
                [
                    'error' => [
                        'message' => $this->get('translator')->trans(
                            'api.login.incorrect_password.text'
                        ),
                    ],
                ],
                400
            );
        }

        $this->container->get('app.user_action_manager')
            ->add(
                'user.api.login',
                $this->translator->trans(
                    'api.login.user_action.text'
                ),
                [],
                $user
            );

        return $this->json([
            'data' => $user->toArray(),
        ]);
    }
}
