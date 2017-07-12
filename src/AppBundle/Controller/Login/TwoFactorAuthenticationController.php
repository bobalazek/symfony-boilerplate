<?php

namespace AppBundle\Controller\Login;

use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Exception\BruteForceAttemptException;
use AppBundle\Entity\User;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class TwoFactorAuthenticationController extends Controller
{
    /**
     * @Route("/login/two-factor-authentication", name="login.two_factor_authentication")
     */
    public function loginTwoFactorAuthenticationAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session = $this->get('session');

        if (!$session->get('two_factor_authentication_in_progress')) {
            $this->addFlash(
                'info',
                $this->get('translator')->trans('general.already_logged_in')
            );

            return $this->redirectToRoute('home');
        }

        $method = $session->get('two_factor_authentication_method');
        $alternativeMethods = $this->getAlternativeMethods($method);
        $response = $this->handleTwoFactorAuthenticationLogin(
            $method,
            $request,
            $session,
            $em
        );
        if ($response) {
            return $response;
        }

        return $this->render(
            'AppBundle:Content:login/two_factor_authentication.html.twig',
            [
                'method' => $method,
                'alternative_methods' => $alternativeMethods,
                'code' => $request->query->get('code'),
            ]
        );
    }

    /**
     * @param string        $method
     * @param Request       $request
     * @param Session       $session
     * @param EntityManager $em
     *
     * @return Response
     */
    private function handleTwoFactorAuthenticationLogin(
        $method,
        Request $request,
        Session $session,
        EntityManager $em
    ) {
        if ($method === 'email') {
            if ($request->getMethod() === 'POST') {
                try {
                    $this->checkIfTooManyAttempts($request, $session, $em);
                } catch (BruteForceAttemptException $e) {
                    $this->addFlash(
                        'danger',
                        $e->getMessage()
                    );

                    return $this->redirectToRoute(
                        'login.two_factor_authentication'
                    );
                }

                $code = $request->request->get('code');
                $userLoginCodeExists = $this->get('app.user_login_code_manager')
                    ->exists($code, $this->getUser());

                if (!$userLoginCodeExists) {
                    $this->addFlash(
                        'danger',
                        $this->get('translator')->trans(
                            'login.two_factor_authentication.code_not_found'
                        )
                    );

                    $this->get('app.user_action_manager')->add(
                        'user.login.2fa.fail',
                        $this->get('translator')->trans(
                            'my.login.2fa.fail.text'
                        ),
                        [
                            'code' => $code,
                        ]
                    );

                    $this->get('app.brute_force_manager')
                        ->handleUserLoginBlocks(
                            $this->getUser(),
                            'login.2fa',
                            'user.login.2fa.fail'
                        );

                    return null;
                }

                $response = $this->redirectToRoute('home');

                $this->addFlash(
                    'success',
                    $this->get('translator')->trans(
                        'login.two_factor_authentication.success'
                    )
                );

                $this->get('app.user_action_manager')->add(
                    'user.login.2fa',
                    'User has been logged in via Two-factor authentication!'
                );

                $session->remove('two_factor_authentication_in_progress');

                return $response;
            }
        }

        return null;
    }

    /**
     * @param Request       $request
     * @param Session       $session
     * @param EntityManager $em
     *
     * @throws BruteForceAttemptHttpException
     */
    private function checkIfTooManyAttempts(
        Request $request,
        Session $session,
        EntityManager $em
    ) {
        $ip = $request->getClientIp();
        $sessionId = $session->getId();
        $userAgent = $request->headers->get('User-Agent');
        $dateTimeFormat = $this->getParameter('date_time_format');

        $userLoginBlock = $em->getRepository('AppBundle:UserLoginBlock')
            ->getCurrentlyActive(
                $ip,
                $sessionId,
                $userAgent,
                'login.2fa'
            );
        if ($userLoginBlock) {
            throw new BruteForceAttemptException(
                $this->get('translator')->trans(
                    'Your account has been blocked from logging it. The block will be released at %time%',
                    [
                        '%time%' => $userLoginBlock->getExpiresAt()->format($dateTimeFormat),
                    ]
                )
            );
        }
    }

    /**
     * @return array
     */
    private function getAlternativeMethods($currentMethod)
    {
        $availableMethods = $this->getUser()
            ->getAvailableTwoFactorAuthenticationMethods();

        // Ignore the current method
        if (isset($availableMethods[$currentMethod])) {
            unset($availableMethods[$currentMethod]);
        }

        return array_keys($availableMethods);
    }
}
