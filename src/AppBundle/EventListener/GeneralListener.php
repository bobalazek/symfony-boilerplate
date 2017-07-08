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
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $em = $this->container->get('doctrine.orm.entity_manager');

        if (
            $event->isMasterRequest() &&
            $tokenStorage->getToken()
        ) {
            $token = $tokenStorage->getToken();
            $user = $token->getUser();

            if ($token->getUser() instanceof User) {
                // Two factor authentication
                if ($session->get('two_factor_authentication_in_progress')) {
                    $accessMap = $this->container->get('security.access_map');
                    $patterns = $accessMap->getPatterns($request);
                    $roles = $patterns[0];

                    // Prevent the gate kicking in on pages, that do not require authentication
                    if ($roles === null) {
                        return false;
                    }

                    $twoFactorAuthenticationRoute = 'login.two_factor_authentication';
                    if ($twoFactorAuthenticationRoute === $event->getRequest()->get('_route')) {
                        return false;
                    }

                    $url = $this->container->get('router')
                        ->generate($twoFactorAuthenticationRoute);
                    $response = new RedirectResponse($url);
                    $event->setController(function() use($response) {
                        return $response;
                    });

                    return false;
                }

                // Last active
                $user->setLastActiveAt(new \DateTime());
                // TODO: setLastActiveAt also for the trusted device

                $em->persist($user);
                $em->flush();
            }
        }
    }
}
