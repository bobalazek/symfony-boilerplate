<?php

namespace AppBundle\Manager;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Jenssegers\Agent\Agent;
use AppBundle\Entity\User;
use AppBundle\Entity\UserTrustedDevice;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class UserTrustedDeviceManager
{
    use ContainerAwareTrait;

    private $cookieName;
    private $cookieLifetime;

    /**
     * Prepare variables.
     */
    public function prepareVariables()
    {
        $trustedDevicesParameters = $this->container->getParameter('trusted_devices');
        $this->cookieName = $trustedDevicesParameters['cookie_name'];
        $this->cookieLifetime = $trustedDevicesParameters['cookie_lifetime'];
    }

    /**
     * Add a new trusted device for that user.
     *
     * @param User   $user
     * @param string $token
     */
    public function add(User $user, $token)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $session = $this->container->get('session');
        $agent = new Agent();

        $userAgentString = $request->headers->get('User-Agent');
        $sessionId = $session->getId();
        $expiresAt = (new \Datetime())->add(
            new \Dateinterval('PT'.$this->cookieLifetime.'S')
        );
        $agent->setUserAgent($userAgentString);
        $name = $agent->platform().' - '.$agent->browser();

        $userTrustedDevice = new UserTrustedDevice();
        $userTrustedDevice
            ->setName($name)
            ->setToken($token)
            ->setIp($request->getClientIp())
            ->setUserAgent($userAgentString)
            ->setSessionId($sessionId)
            ->setExpiresAt($expiresAt)
            ->setUser($user)
        ;

        $em->persist($userTrustedDevice);
        $em->flush();

        return $userTrustedDevice;
    }

    /**
     * Determine if that is one of the trusted devices.
     *
     * @param User $user
     *
     * @return bool
     */
    public function is(User $user)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $trustedDevicesParameters = $this->container->getParameter('trusted_devices');

        if ($request->cookies->has($this->cookieName)) {
            $tokens = explode(';', $request->cookies->get($this->cookieName));
            foreach ($tokens as $token) {
                $userTrustedDevice = $em->getRepository('AppBundle:UserTrustedDevice')
                    ->findOneBy([
                        'user' => $user,
                        'token' => $token,
                    ]);

                if (
                    $userTrustedDevice !== null &&
                    !$userTrustedDevice->isExpired() &&
                    !$userTrustedDevice->isDeleted()
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Creates the cookie for that trusted device.
     *
     * @param string  $token
     * @param Request $request
     *
     * @return Cookie
     */
    public function createCookie($token, Request $request)
    {
        $tokens = $request->cookies->get($this->cookieName);
        $tokens .= ($tokens !== null ? ';' : '').$token;
        $expiresAt = (new \Datetime())->add(new \Dateinterval('PT'.$this->cookieLifetime.'S'));

        $domain = null;
        $host = $request->getHost();
        if ($host !== 'localhost') {
            $domain = '.'.$host;
        }

        return new Cookie(
            $this->cookieName,
            $tokens,
            $expiresAt,
            '/',
            $domain
        );
    }
}
