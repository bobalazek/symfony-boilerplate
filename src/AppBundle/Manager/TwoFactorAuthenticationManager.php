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

            $this->container
                ->get('app.user_action_manager')
                ->add(
                    'user.login.2fa.gate',
                    'User has been logged in, but still needs to confirm 2FA!'
                );

            $this->container
                ->get('event_dispatcher')
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
        $session->set(
            'two_factor_authentication_method',
            $method
        );

        if ($method === 'email') {
            $code = Helpers::getRandomString(8);

            $userLoginCode = $this->container
                ->get('app.user_login_code_manager')
                ->add($code, $method);

            $this->container
                ->get('app.mailer')
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
                        'user_login_code' => $userLoginCode,
                    ],
                ])
            ;
        }

        return true;
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
        $event->setResponse(
            new RedirectResponse(
                $router->generate('login.tfa')
            )
        );
    }
}
