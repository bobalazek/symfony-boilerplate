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
            !$event->isMasterRequest() ||
            !$tokenStorage->getToken()
        ) {
            return false;
        }

        $token = $tokenStorage->getToken();
        $user = $token->getUser();

        if (!($token->getUser() instanceof User)) {
            return false;
        }

        // Two factor authentication
        if ($session->get('two_factor_authentication_in_progress')) {
            $accessMap = $this->container->get('security.access_map');
            $patterns = $accessMap->getPatterns($request);
            $roles = $patterns[0];

            // Prevent the 2FA gate on pages, that do not require authentication
            if ($roles === null) {
                return false;
            }

            $twoFactorAuthenticationRoute = 'login.tfa';
            if ($twoFactorAuthenticationRoute === $event->getRequest()->get('_route')) {
                return false;
            }

            $url = $this->container->get('router')
                ->generate($twoFactorAuthenticationRoute);
            $event->setController(function () use ($url) {
                return new RedirectResponse($url);
            });

            return false;
        }

        // User - last active
        $user->setLastActiveAt(new \Datetime());
        $em->persist($user);

        // User device - last active
        $deviceUid = $request->cookies->get('device_uid');
        if ($deviceUid) {
            $userDevice = $em->getRepository('AppBundle:UserDevice')
                ->findOneBy([
                    'user' => $user,
                    'uid' => $deviceUid,
                ]);
            if ($userDevice !== nul) {
                $userDevice->setLastActive(new \Datetime());
                $em->persist($userDevice);
            }
        }

        $em->flush();
    }
}
