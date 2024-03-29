<?php

namespace CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
        // Fixes the issue of showing the "already logged in" flash directly after the login.
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
                    $this->get('translator')->trans(
                        'general.already_logged_in'
                    )
                );
            }

            return $this->redirectToRoute('home');
        }

        $authenticationUtils = $this->get('security.authentication_utils');
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render(
            'CoreBundle:Content:login.html.twig',
            [
                'last_username' => $lastUsername,
                'error' => $error,
            ]
        );
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
