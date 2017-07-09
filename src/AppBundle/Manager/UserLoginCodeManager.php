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
     * @return bool
     */
    public function add($code, $method = 'email', User $user = null)
    {
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

        $userLoginCode = new UserLoginCode();
        $userLoginCode
            ->setCode($code)
            ->setMethod($method)
            ->setIp($request->getClientIp())
            ->setUserAgent($request->headers->get('User-Agent'))
            ->setSessionId($sessionId)
            ->setUser($user)
        ;

        $em->persist($userLoginCode);
        $em->flush();

        return true;
    }
}
