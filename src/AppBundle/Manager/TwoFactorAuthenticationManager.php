<?php

namespace AppBundle\Manager;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use AppBundle\Entity\User;
use AppBundle\Utils\Helpers;

/**
 * Some methods are taken from https://github.com/scheb/two-factor-bundle/blob/master/Security/TwoFactor/Provider/Google/GoogleAuthenticator.php.
 *
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class TwoFactorAuthenticationManager
{
    use ContainerAwareTrait;

    /**
     * @param InteractiveLoginEvent $event
     *
     * @return bool Should we continue propagation?
     */
    public function handle(InteractiveLoginEvent $event)
    {
        $session = $this->container->get('session');
        $user = $event->getAuthenticationToken()->getUser();

        $isEnabled = $user->isTFAEnabled();
        $availableMethods = $user->getAvailableTFAMethods();
        if (
            $isEnabled &&
            !empty($availableMethods)
        ) {
            $method = $user->getTFADefaultMethod();

            $this->handleMethod($method, $user);

            $session->set(
                'two_factor_authentication_in_progress',
                true
            );
            $session->set(
                'two_factor_authentication_method',
                $method
            );

            $this->container->get('app.user_action_manager')->add(
                'user.login.2fa.gate',
                'User has been logged in, but still needs to confirm 2FA!'
            );

            $this->container->get('event_dispatcher')
                ->addListener(
                    KernelEvents::RESPONSE,
                    [$this, 'onKernelResponse']
                );

            return false;
        }

        return true;
    }

    /**
     * Handle all the method related stuff.
     *
     * @param string $method
     * @param User   $user
     *
     * @return bool
     */
    public function handleMethod($method, User $user)
    {
        if ($method === 'email') {
            $code = Helpers::getRandomString(16);

            $this->container->get('app.user_login_code_manager')->add($code, $method);

            $this->container->get('app.mailer')
                ->swiftMessageInitializeAndSend([
                    'subject' => $this->container->get('translator')->trans(
                        'login.2fa.email.subject',
                        [
                            '%app_name%' => $this->container->getParameter('app_name'),
                        ]
                    ),
                    'to' => [$user->getEmail() => $user->getName()],
                    'body' => 'AppBundle:Emails:User/login/2fa.html.twig',
                    'template_data' => [
                        'user' => $user,
                        'code' => $code,
                    ],
                ])
            ;
        }

        return true;
    }

    /**
     * Validates the code, which was entered by the user.
     *
     * @param User   $user
     * @param string $code
     *
     * @return bool
     */
    public function checkCode(User $user, $code)
    {
        $secret = $user->getTFAAuthenticatorSecret();

        return $this->container->get('google_authenticator')
            ->checkCode(
                $secret,
                $code
            );
    }

    /**
     * Generate the URL of a QR code, which can be scanned by an two factor authenticator app.
     *
     * @param User $user
     *
     * @return string
     */
    public function getUrl(User $user)
    {
        $encoder = 'https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl=';

        return $encoder.urlencode($this->getQRContent($user));
    }

    /**
     * Generate the content for a QR-Code to be scanned by the tow-factor authenticator.
     * Use this method if you don't want to use google charts to display the qr-code.
     *
     * @param User $user
     *
     * @return string
     */
    public function getQRContent(User $user)
    {
        $tfaParameters = $this->container
            ->getParameter('two_factor_authentication');
        $hostname = $tfaParameters['authenticator_hostname'];
        $issuer = $tfaParameters['authenticator_issuer'];
        $secret = $user->getTFAAuthenticatorSecret();

        $userAndHost = rawurlencode($user->getUsername()).
            ($hostname ? '@'.rawurlencode($hostname) : '');

        if ($issuer) {
            $qrContent = sprintf(
                'otpauth://totp/%s:%s?secret=%s&issuer=%s',
                rawurlencode($issuer),
                $userAndHost,
                $secret,
                rawurlencode($issuer)
            );
        } else {
            $qrContent = sprintf(
                'otpauth://totp/%s?secret=%s',
                $userAndHost,
                $secret
            );
        }

        return $qrContent;
    }

    /**
     * Generate a new secret for the two-factor authenticator.
     *
     * @return string
     */
    public function generateSecret()
    {
        return $this->container->get('google_authenticator')
            ->generateSecret();
    }

    /***** Events *****/

    /**
     * Redirect it directly to the two factor authentication route.
     *
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $router = $this->container->get('router');
        $response = new RedirectResponse(
            $router->generate('login.two_factor_authentication')
        );
        $event->setResponse($response);
    }
}
