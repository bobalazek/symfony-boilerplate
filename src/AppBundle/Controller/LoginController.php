<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use AppBundle\Utils\Helpers;

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
        $session = $this->get('session');

        if (!$session->get('two_factor_authentication_in_progress')) {
            $this->addFlash(
                'info',
                $this->get('translator')->trans('general.already_logged_in')
            );

            return $this->redirectToRoute('home');
        }

        $method = $session->get('two_factor_authentication_method');
        $success = $this->handleTwoFactorAuthenticationLogin(
            $method,
            $request,
            $session
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
     * @param string  $method
     * @param Request $request
     * @param Session $session
     *
     * @return bool Was the authorization successful?
     */
    private function handleTwoFactorAuthenticationLogin($method, Request $request, Session $session)
    {
        if ($method === 'email') {
            if ($request->getMethod() === 'POST') {
                // TODO: check if the user has tried to enter too many times

                $em = $this->getDoctrine()->getManager();
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

                    // TODO: log failed login attempt

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
