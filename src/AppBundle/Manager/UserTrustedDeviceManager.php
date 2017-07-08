<?php

namespace AppBundle\Manager;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Cookie;
use Jenssegers\Agent\Agent;
use AppBundle\Entity\User;
use AppBundle\Entity\UserTrustedDevice;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class UserTrustedDeviceManager
{
    use ContainerAwareTrait;

    public $cookieLifetime = 5184000; // 60 days
    public $cookieName = 'trusted_device';

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

        $em->persist($userAction);
        $em->flush();

        $cookie = $this->createCookie($token);

        return $userAction;
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
     * @param string $token
     *
     * @return Cookie
     */
    public function createCookie($token)
    {
        $token = $this->getRandomString(32);

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

    /**
     * Creates the cookie for that trusted device.
     *
     * @param int $length
     *
     * @return string
     */
    public function getRandomString($length = 16)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $string = '';

        for ($i = 0; $i < $length; ++$i) {
            $string .= $characters[mt_rand(0, strlen($characters) - 1)];
        }

        return $string;
    }
}
