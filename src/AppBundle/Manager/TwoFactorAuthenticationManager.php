<?php

namespace AppBundle\Manager;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use AppBundle\Entity\User;
use AppBundle\Utils\Helpers;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class TwoFactorAuthenticationManager
{
    use ContainerAwareTrait;

    /**
     * @param InteractiveLoginEvent $event
     *
     * @return bool Continue propagation?
     */
    public function handle(InteractiveLoginEvent $event)
    {
        $session = $this->container->get('session');
        $user = $event->getAuthenticationToken()->getUser();

        $isTwoFactorAuthenticationEnabled = $user->isTwoFactorAuthenticationEnabled();
        if ($isTwoFactorAuthenticationEnabled) {
            $twoFactorAuthenticationMethod = $user->getTwoFactorAuthenticationDefaultMethod();

            $this->handleMethod($twoFactorAuthenticationMethod, $user);

            $session->set(
                'two_factor_authentication_in_progress',
                true
            );
            $session->set(
                'two_factor_authentication_method',
                $twoFactorAuthenticationMethod
            );

            $this->container->get('app.user_action_manager')->add(
                'user.login.2fa',
                'User has been logged in, but still needs to confirm 2FA!'
            );

            return false;
        }

        return true;
    }

    /**
     * Handle all the method related stuff.
     *
     * @param string $method
     * @param User $user
     *
     * @return bool
     */
    public function handleMethod($method, User $user)
    {
        if ($method === 'email') {
            $code = Helpers::getRandomString(16);
            $this->container->get('app.mailer')
                ->swiftMessageInitializeAndSend([
                    'subject' => $this->container->get('translator')->trans(
                        'login_2fa.email.subject',
                        [
                            '%app_name%' => $this->container->getParameter('app_name'),
                        ]
                    ),
                    'to' => [$user->getEmail() => $user->getName()],
                    'body' => 'AppBundle:Emails:User/login_2fa.html.twig',
                    'template_data' => [
                        'user' => $user,
                        'code' => $code,
                    ],
                ])
            ;
        }

        return true;
    }
}
