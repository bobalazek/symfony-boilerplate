<?php

namespace AppBundle\Service;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use AppBundle\Entity\User;
use AppBundle\Entity\UserAction;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class UserActionsService
{
    use ContainerAwareTrait;

    /**
     * @param string $key
     * @param string $message
     * @param array  $data
     *
     * @return bool
     */
    public function add($key, $message, array $data = [], User $user = null)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $token = $this->container->get('security.token_storage')->getToken();

        if (
            $user === null &&
            $token !== null &&
            $token->getUser() instanceof User
        ) {
            $user = $token->getUser();
        }

        $request = $this->container->get('request_stack')->getCurrentRequest();

        $session = $this->container->get('session');
        $sessionId = $session->getId();

        $userAction = new UserAction();
        $userAction
            ->setUser($user)
            ->setKey($key)
            ->setMessage($message)
            ->setData($data)
            ->setIp($request->getClientIp())
            ->setUserAgent($request->headers->get('User-Agent'))
            ->setSessionId($sessionId)
        ;

        $em->persist($userAction);
        $em->flush();

        return true;
    }
}
