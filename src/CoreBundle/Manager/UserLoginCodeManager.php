<?php

namespace CoreBundle\Manager;

use Symfony\Component\DependencyInjection\ContainerInterface;
use CoreBundle\Entity\User;
use CoreBundle\Entity\UserLoginCode;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class UserLoginCodeManager
{
    /** @var ContainerInterface */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $code
     * @param string $method
     * @param User   $user
     *
     * @return UserLoginCode
     */
    public function add($code, $method = 'email', User $user = null)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $token = $this->container->get('security.token_storage')->getToken();
        $session = $this->container->get('session');
        $loginCodeExpiryTime = $this->container->getParameter(
            'login_code_expiry_time'
        );

        if (
            null === $user &&
            null !== $token &&
            $token->getUser() instanceof User
        ) {
            $user = $token->getUser();
        }

        $request = $this->container->get('request_stack')->getCurrentRequest();
        $sessionId = $session->getId();
        $expiresAt = (new \Datetime())->add(
            new \Dateinterval('PT' . $loginCodeExpiryTime . 'S')
        );

        $userLoginCode = new UserLoginCode();
        $userLoginCode
            ->setCode($code)
            ->setType($method)
            ->setIp($request->getClientIp())
            ->setUserAgent($request->headers->get('User-Agent'))
            ->setSessionId($sessionId)
            ->setExpiresAt($expiresAt)
            ->setUser($user)
        ;

        $em->persist($userLoginCode);
        $em->flush();

        return $userLoginCode;
    }

    /**
     * If there is any valid user login code.
     *
     * @param string $code
     * @param User   $user
     *
     * @return bool
     */
    public function exists($code, User $user)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $userLoginCode = $em
            ->getRepository('CoreBundle:UserLoginCode')
            ->findOneBy([
                'code' => $code,
                'user' => $user,
            ]);
        if (
            null !== $userLoginCode &&
            false === $userLoginCode->isExpired() &&
            false === $userLoginCode->isDeleted()
        ) {
            return true;
        }

        return false;
    }
}
