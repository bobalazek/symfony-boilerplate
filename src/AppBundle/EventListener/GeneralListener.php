<?php

namespace AppBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Jenssegers\Agent\Agent;
use AppBundle\Entity\User;
use AppBundle\Entity\UserDevice;
use AppBundle\Utils\Helpers;

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
        $session = $this->container->get('session');
        $tokenStorage = $this->container->get('security.token_storage');
        $request = $this->container->get('request_stack')->getCurrentRequest();

        if (
            !$event->isMasterRequest() ||
            !$tokenStorage->getToken()
        ) {
            return false;
        }

        $token = $tokenStorage->getToken();
        $user = $token->getUser();

        if (!($user instanceof User)) {
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

        $em = $this->container->get('doctrine.orm.entity_manager');

        // User - last active
        $user->setLastActiveAt(new \Datetime());
        $em->persist($user);

        // User device - last active
        $deviceUid = $request->query->has('device_uid')
            ? $request->query->get('device_uid')
            : ($request->cookies->has('device_uid')
                ? $request->cookies->get('device_uid')
                : ($request->headers->has('X-Device-UID')
                    ? $request->headers->get('X-Device-UID')
                    : null
                )
            );
        if ($deviceUid) {
            $userDevice = $em->getRepository('AppBundle:UserDevice')
                ->findOneBy([
                    'user' => $user,
                    'uid' => $deviceUid,
                ]);

            if ($userDevice === null) {
                $userDevice = $this->createUserDevice(
                    $request,
                    $this->container->get('session'),
                    $user
                );
            }

            $userDevice->setLastActiveAt(new \Datetime());

            $em->persist($userDevice);
        }

        $em->flush();
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $tokenStorage = $this->container->get('security.token_storage');
        $request = $event->getRequest();

        if (
            !$event->isMasterRequest() ||
            !$tokenStorage->getToken()
        ) {
            return false;
        }

        $token = $tokenStorage->getToken();
        $user = $token->getUser();

        if (!($user instanceof User)) {
            return false;
        }

        if ($request->cookies->has('device_uid')) {
            return false;
        }

        $em = $this->container->get('doctrine.orm.entity_manager');
        $userDevice = $this->createUserDevice(
            $request,
            $this->container->get('session'),
            $user
        );
        $em->persist($userDevice);
        $em->flush();
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        $cookieLifetime = 311040000; // 10 years
        $deviceUid = $request->attributes->get('device_uid');

        if (!$deviceUid) {
            return false;
        }

        $cookie = new Cookie('device_uid', $deviceUid, time() + $cookieLifetime);
        $response->headers->setCookie($cookie);
    }

    /**
     * Creates a user device.
     *
     * @param Request $request
     * @param Session $session
     * @param User    $user
     *
     * @return UserDevice
     */
    protected function createUserDevice(
        Request $request,
        Session $session,
        User $user
    ) {
        $deviceUid = Helpers::getRandomString(64);

        $userAgent = $request->headers->get('User-Agent');
        $agent = new Agent();
        $agent->setUserAgent($userAgent);

        $userDevice = new UserDevice();
        $userDevice
            ->setUid($deviceUid)
            ->setName($agent->platform().' - '.$agent->browser())
            ->setIp($request->getClientIp())
            ->setUserAgent($userAgent)
            ->setSessionId($session->getId())
            ->setUser($user)
        ;

        $request->attributes->set('device_uid', $userDevice->getUid());

        return $userDevice;
    }
}
