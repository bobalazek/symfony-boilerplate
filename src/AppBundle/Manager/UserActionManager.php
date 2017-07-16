<?php

namespace AppBundle\Manager;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use AppBundle\Entity\User;
use AppBundle\Entity\UserAction;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class UserActionManager
{
    use ContainerAwareTrait;

    /**
     * @param string $key
     * @param string $message
     * @param array  $data
     * @param User   $user
     * @param bool   $handleBlockedActions should it check if the action was executed too much times? Create a block if so
     * @param string $action               What is the action key?
     *
     * @return bool
     */
    public function add(
        $key,
        $message,
        array $data = [],
        User $user = null,
        $handleBlockedActions = false,
        $action = null
    ) {
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

        $userAction = new UserAction();
        $userAction
            ->setKey($key)
            ->setMessage($message)
            ->setData($data)
            ->setIp($request->getClientIp())
            ->setUserAgent($request->headers->get('User-Agent'))
            ->setSessionId($sessionId)
            ->setUser($user)
        ;

        $em->persist($userAction);
        $em->flush();

        if ($handleBlockedActions) {
            $this->container->get('app.brute_force_manager')
                ->handleUserBlockedAction(
                    $user,
                    $action,
                    $key
                );
        }

        return true;
    }
}
