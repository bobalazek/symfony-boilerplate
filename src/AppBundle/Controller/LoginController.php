<?php

namespace AppBundle\Controller;

use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use AppBundle\Utils\Helpers;
use AppBundle\Exception\BruteForceAttemptHttpException;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class LoginController extends Controller
{
    /**
     * @Route("/login", name="login")
     */
    public function loginAction(Request $request)
    {
        if ($this->isGranted('ROLE_USER')) {
            $referer = $request->headers->get('referer');
            $loginUrl = $this->generateUrl(
                'login',
                [],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            if ($referer !== $loginUrl) {
                $this->addFlash(
                    'info',
                    $this->get('translator')->trans('general.already_logged_in')
                );
            }

            return $this->redirectToRoute('home');
        }

        $authenticationUtils = $this->get('security.authentication_utils');
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render(
            'AppBundle:Content:login.html.twig',
            [
                'last_username' => $lastUsername,
                'error' => $error,
            ]
        );
    }

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

        // Check if we are blocked
        $dateTimeFormat = $this->getParameter('date_time_format');
        $ip = $request->getClientIp();
        $sessionId = $session->getId();
        $userAgent = $request->headers->get('User-Agent');

        $userLoginBlock = $em->getRepository('AppBundle:UserLoginBlock')
            ->getCurrentlyActive(
                $ip,
                $sessionId,
                $userAgent,
                'login.2fa'
            );
        if ($userLoginBlock) {
            throw new BruteForceAttemptHttpException(
                $this->get('translator')->trans(
                    'Your account has been blocked from logging it. The block will be released at %time%',
                    [
                        '%time%' => $userLoginBlock->getExpiresAt()->format($dateTimeFormat),
                    ]
                )
            );
        }

        $method = $session->get('two_factor_authentication_method');
        $success = $this->handleTwoFactorAuthenticationLogin(
            $method,
            $request,
            $session,
            $em
        );
        if ($success) {
            $this->addFlash(
                'success',
                $this->get('translator')->trans(
                    'login.two_factor_authentication.success'
                )
            );

            return $this->redirectToRoute('home');
        }

        return $this->render(
            'AppBundle:Content:login/two_factor_authentication.html.twig',
            [
                'method' => $method,
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
     * @return bool Was the authorization successful?
     */
    private function handleTwoFactorAuthenticationLogin($method, Request $request, Session $session, EntityManager $em)
    {
        if ($method === 'email') {
            if ($request->getMethod() === 'POST') {
                $code = $request->request->get('code');
                $isTrustedDevice = $request->request->get('is_trusted_device') === 'yes';

                $userLoginCode = $em->getRepository('AppBundle:UserLoginCode')
                    ->findOneBy([
                        'user' => $this->getUser(),
                        'code' => $code,
                    ]);

                if (!$userLoginCode) {
                    $this->addFlash(
                        'danger',
                        $this->get('translator')->trans(
                            'login.two_factor_authentication.code_not_found'
                        )
                    );

                    $this->get('app.user_action_manager')->add(
                        'user.login.2fa.fail',
                        $this->get('translator')->trans('my.login.2fa.fail.text'),
                        [
                            'code' => $code,
                        ]
                    );

                    $this->get('app.brute_force_manager')->handleUserLoginBlocks(
                        $this->getUser(),
                        'login.2fa',
                        'user.login.2fa.fail'
                    );

                    return false;
                }

                if ($isTrustedDevice) {
                    $this->get('app.user_trusted_device_manager')->add(
                        $this->getUser(),
                        Helpers::getRandomString(64)
                    );
                }

                $session->remove('two_factor_authentication_in_progress');

                return true;
            }
        }

        return false;
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logoutAction(Request $request)
    {
        $this->get('security.token_storage')->setToken(null);

        return $this->redirectToRoute('login');
    }
}
