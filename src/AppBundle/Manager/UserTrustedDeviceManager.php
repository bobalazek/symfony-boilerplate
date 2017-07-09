<?php

namespace AppBundle\Manager;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Jenssegers\Agent\Agent;
use AppBundle\Entity\User;
use AppBundle\Entity\UserTrustedDevice;
use AppBundle\Utils\Helpers;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class UserTrustedDeviceManager
{
    use ContainerAwareTrait;

    public $cookieLifetime;
    public $cookieName;

    public function __construct()
    {
        $this->cookieLifetime = $this->container->getParameter('trusted_devices.cookie_lifetime');
        $this->cookieName = $this->container->getParameter('trusted_devices.cookie_name');
    }

    /**
     * Add a new trusted device for that user.
     *
     * @param User   $user
     * @param string $token
     * @param string $name
     */
    public function add(User $user, $token, $name = null)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $userAgentString = $request->headers->get('User-Agent');
        $session = $this->container->get('session');
        $sessionId = $session->getId();
        $expiresAt = (new \Datetime())->add(new \Dateinterval('PT'.$this->cookieLifetime.'S'));

        if ($name === null) {
            $agent = new Agent();
            $agent->setUserAgent($userAgentString);

            $name = $agent->platform().' - '.$agent->browser();
        }

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

        $cookie = $this->createCookie($token, $request);

        return $userTrustedDevice;
    }

    /**
     * Determine if that is one of the trusted devices.
     *
     * @param User   $user
     * @param string $token
     *
     * @return bool
     */
    public function is(User $user, $token)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $repository = $em->getRepository('AppBundle:TrustedDeviceManager');
        $request = $this->container->get('request_stack')->getCurrentRequest();

        $userTrustedDevice = $repository->findOneBy([
            'user' => $user,
            'token' => $token,
        ]);

        if (
            $userTrustedDevice !== null &&
            $userTrustedDevice->getExpiresAt() < new \Datetime() &&
            !$userTrustedDevice->isDeleted()
        ) {
            return true;
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
        $token = Helpers::getRandomString(32);

        $tokenList = $request->cookies->get($this->cookieName);
        $tokenList .= ($tokenList !== null ? ';' : '').$token;
        $expiresAt = (new \Datetime())->add(new \Dateinterval('PT'.$this->cookieLifetime.'S'));

        $domain = null;
        $host = $request->getHost();
        if ($host !== 'localhost') {
            $domain = '.'.$host;
        }

        return new Cookie(
            $this->cookieName,
            $tokenList,
            $expiresAt,
            '/',
            $domain
        );
    }
}
