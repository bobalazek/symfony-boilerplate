<?php

namespace CoreBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Cookie;
use CoreBundle\Entity\User;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class GeneralListener
{
    use ContainerAwareTrait;

    /**
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $tokenStorage = $this->container->get('security.token_storage');
        $request = $event->getRequest();

        if (
            $event->isMasterRequest() === false ||
            $tokenStorage->getToken() === null
        ) {
            return;
        }

        $token = $tokenStorage->getToken();
        $user = $token->getUser();

        if (!($user instanceof User)) {
            return;
        }

        $em = $this->container->get('doctrine.orm.entity_manager');

        // User - last active
        $user->setLastActiveAt(new \Datetime());
        $em->persist($user);

        // User device - last active
        $userDevice = $this->container
            ->get('app.user_device_manager')
            ->get($user, $request);
        $userDevice->setLastActiveAt(new \Datetime());
        $em->persist($userDevice);

        // Flush database
        $em->flush();
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        /**
         * Create the the user_device cookie if necessary
         *   (will happen only when a new device is created).
         */
        $request = $event->getRequest();
        $response = $event->getResponse();

        $cookieLifetime = 311040000; // 10 years
        $deviceUid = $request->attributes->get(
            'device_uid'
        );

        if ($deviceUid === null) {
            return;
        }

        $cookie = new Cookie(
            'device_uid',
            $deviceUid,
            time() + $cookieLifetime
        );
        $response->headers->setCookie($cookie);
    }
}
