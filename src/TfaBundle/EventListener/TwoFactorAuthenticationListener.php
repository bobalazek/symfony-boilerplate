<?php

namespace TfaBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class TwoFactorAuthenticationListener
{
    use ContainerAwareTrait;

    /**
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $session = $this->container->get('session');
        $request = $event->getRequest();

        if ($session->get('two_factor_authentication_in_progress')) {
            $accessMap = $this->container->get('security.access_map');
            $patterns = $accessMap->getPatterns($request);
            $roles = $patterns[0];

            // Prevent the 2FA gate on pages, that do not require authentication
            if (null === $roles) {
                return;
            }

            $twoFactorAuthenticationRoute = 'login.tfa';
            $route = $request->get('_route');
            if ($twoFactorAuthenticationRoute === $route) {
                return;
            }

            $url = $this->container->get('router')
                ->generate($twoFactorAuthenticationRoute);
            $event->setController(function () use ($url) {
                return new RedirectResponse($url);
            });
        }
    }
}
