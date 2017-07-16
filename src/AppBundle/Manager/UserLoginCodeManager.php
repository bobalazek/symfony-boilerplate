<?php

namespace AppBundle\Manager;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use AppBundle\Entity\User;
use AppBundle\Entity\UserLoginCode;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class UserLoginCodeManager
{
    use ContainerAwareTrait;

    /**
     * @param string $code
     * @param string $method
     * @param User   $user
     *
     * @return UserLoginCode
     */
    public function add($code, $method = 'email', User $user = null)
    {
        $loginCodeParameters = $this->container->getParameter('login_code');
        $em = $this->container->get('doctrine.orm.entity_manager');
        $token = $this->container->get('security.token_storage')->getToken();
        $session = $this->container->get('session');

        if (
            $user === null &&
            $token !== null &&
            $token->getUser() instanceof User
        ) {
            $user = $token->getUser();
        }

        $request = $this->container->get('request_stack')->getCurrentRequest();
        $sessionId = $session->getId();
        $expiresAt = (new \Datetime())->add(
            new \Dateinterval('PT'.$loginCodeParameters['expiry_time'].'S')
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
        $userLoginCode = $em->getRepository('AppBundle:UserLoginCode')
            ->findOneBy([
                'code' => $code,
                'user' => $user,
            ]);
        if (
            $userLoginCode !== null &&
            !$userLoginCode->isExpired() &&
            !$userLoginCode->isDeleted()
        ) {
            return true;
        }

        return false;
    }
}
