<?php

namespace AppBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\RedirectResponse;
use AppBundle\Entity\User;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class GeneralListener
{
    use ContainerAwareTrait;

    public function onKernelController(FilterControllerEvent $event)
    {
        $session = $this->container->get('session');
        $tokenStorage = $this->container->get('security.token_storage');
        $em = $this->container->get('doctrine.orm.entity_manager');

        // Two factor authentication
        if ($session->get('two_factor_authentication_in_progress')) {
            // TODO: only in secured area!

            $twoFactorAuthenticationRoute = 'login.two_factor_authentication';
            if ($route === $event->getRequest()->get('_route')) {
                return false;
            }

            $url = $this->router->generate($twoFactorAuthenticationRoute);
            $response = new RedirectResponse($url);
            $event->setResponse($response);
        }

        // Last active
        if (
            $event->isMasterRequest() &&
            $tokenStorage->getToken()
        ) {
            $token = $tokenStorage->getToken();
            $user = $token->getUser();

            if ($token->getUser() instanceof User) {
                $user->setLastActiveAt(new \DateTime());
                // TODO: setLastActiveAt also for the trusted device

                $em->persist($user);
                $em->flush();
            }
        }
    }
}
