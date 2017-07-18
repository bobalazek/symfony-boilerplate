<?php

namespace AppBundle\Manager;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use libphonenumber\PhoneNumberFormat;
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

        if ($user->isTFAEnabled()) {
            $availableMethods = $user->getAvailableTFAMethods();
            $method = $user->getTFADefaultMethod();

            if (!in_array($method, $availableMethods)) {
                $method = null;
            }

            $session->set(
                'two_factor_authentication_method',
                $method
            );

            $this->handleMethod($method, $user);

            $session->set(
                'two_factor_authentication_in_progress',
                true
            );

            $this->container
                ->get('app.user_action_manager')
                ->add(
                    'user.login.tfa.gate',
                    $this->container->get('translator')->trans(
                        'login.tfa.gate.user_action.text'
                    )
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
        if ($method === 'email') {
            $code = Helpers::getRandomString(8);

            $userLoginCode = $this->container
                ->get('app.user_login_code_manager')
                ->add($code, 'email');

            $this->container
                ->get('app.mailer')
                ->swiftMessageInitializeAndSend([
                    /** @Desc("Available arguments: %app_name%") */
                    'subject' => $this->container->get('translator')->trans(
                        'emails.user.login.tfa.subject',
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
        } elseif ($method === 'sms') {
            $code = Helpers::getRandomString(8);

            $userLoginCode = $this->container
                ->get('app.user_login_code_manager')
                ->add($code, 'sms');

            $to = $this->container
                ->get('libphonenumber.phone_number_util')
                ->format(
                    $user->getMobile(),
                    PhoneNumberFormat::INTERNATIONAL
                );

            $this->container->get('app.sms_sender')
                ->send(
                    $to,
                    /** @Desc("Available arguments: %code%") */
                    $this->container->get('translator')->trans(
                        'login.tfa.sms.text',
                        [
                            '%code%' => $code,
                        ]
                    )
                );
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
