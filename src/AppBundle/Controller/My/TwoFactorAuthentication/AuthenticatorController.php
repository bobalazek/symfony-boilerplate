<?php

namespace AppBundle\Controller\My\TwoFactorAuthentication;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class AuthenticatorController extends Controller
{
    /**
     * @Route("/my/tfa/authenticator", name="my.tfa.authenticator")
     * @Security("has_role('ROLE_USER')")
     */
    public function authenticatorAction(Request $request) {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $session = $this->get('session');
        $twoFactorAuthenticatorManager = $this
            ->get('app.two_factor_authenticator_manager');

        $secret = $user->getTFAAuthenticatorSecret();

        if ($secret === null) {
            $secret = $twoFactorAuthenticatorManager
                ->generateSecret();
            $user->setTFAAuthenticatorSecret($secret);

            $em->persist($user);
            $em->flush();
        }

        $qrCodeUrl = $twoFactorAuthenticatorManager
            ->getUrl($user);

        if ($request->getMethod() === 'POST') {
            $code = $request->request->get('code');

            $codeIsValid = $twoFactorAuthenticatorManager
                ->checkCode(
                    $user,
                    $code
                );

            if (!$codeIsValid) {
                $this->addFlash(
                    'danger',
                    $this->get('translator')->trans(
                        'my.tfa.authenticator.invalid_code.text'
                    )
                );

                return $this->redirectToRoute('my.tfa.authenticator');
            }

            $this->addFlash(
                'success',
                $this->get('translator')->trans(
                    'my.tfa.authenticator.success.text'
                )
            );

            $user->setTFAAuthenticatorActivatedAt(
                new \Datetime()
            );

            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('my.tfa.authenticator');
        }

        return $this->render(
            'AppBundle:Content:my/tfa/authenticator.html.twig',
            [
                'qr_code_url' => $qrCodeUrl,
                'secret' => $secret,
            ]
        );
    }
}
