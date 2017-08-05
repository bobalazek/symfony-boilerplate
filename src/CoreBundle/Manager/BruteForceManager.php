<?php

namespace CoreBundle\Manager;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Request;
use CoreBundle\Exception\BruteForceAttemptException;
use CoreBundle\Entity\User;
use CoreBundle\Entity\UserBlockedAction;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class BruteForceManager
{
    use ContainerAwareTrait;

    /**
     * @return bool
     */
    public function checkIfBlocked(Request $request, $action = 'login')
    {
        $session = $this->container->get('session');
        $em = $this->container->get('doctrine.orm.entity_manager');

        $ip = $request->getClientIp();
        $sessionId = $session->getId();
        $userAgent = $request->headers->get('User-Agent');

        $userBlockedAction = $em->getRepository('CoreBundle:UserBlockedAction')
            ->getCurrentlyActive(
                $ip,
                $sessionId,
                $userAgent,
                $action
            );
        if ($userBlockedAction) {
            throw new BruteForceAttemptException(
                /* @Meaning("Available arguments: %time%, %action%") */
                $this->container->get('translator')->trans(
                    'brute_force.attempt.text',
                    [
                        '%time%' => $userBlockedAction->getExpiresAt()->format(
                            $this->container->getParameter('date_time_format')
                        ),
                        '%action%' => $action,
                    ]
                )
            );
        }

        return true;
    }

    /**
     * @param User   $user
     * @param string $type
     * @param string $actionKey
     *
     * @return bool
     */
    public function handleUserBlockedAction(
        User $user = null,
        $action = 'login',
        $userActionKey = 'user.login.fail'
    ) {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $session = $this->container->get('session');

        $bruteForceWatchTime = $this->container->getParameter(
            'brute_force_watch_time'
        );
        $bruteForceMaxAttempts = $this->container->getParameter(
            'brute_force_max_attempts'
        );
        $bruteForceBlockTime = $this->container->getParameter(
            'brute_force_block_time'
        );

        $ip = $request->getClientIp();
        $sessionId = $session->getId();
        $userAgent = $request->headers->get('User-Agent');

        $attemptsCount = $em->getRepository('CoreBundle:UserAction')
            ->getCount(
                $userActionKey,
                $bruteForceWatchTime,
                $ip,
                $sessionId,
                $userAgent
            );

        if ($attemptsCount > $bruteForceMaxAttempts) {
            $expiresAt = (new \Datetime())->add(
                new \Dateinterval('PT'.$bruteForceBlockTime.'S')
            );

            $userBlockedAction = $em
                ->getRepository('CoreBundle:UserBlockedAction')
                ->getCurrentlyActive(
                    $ip,
                    $sessionId,
                    $userAgent,
                    $type
                );

            if ($userBlockedAction === null) {
                $userBlockedAction = new UserBlockedAction();
                $userBlockedAction
                    ->setAction($action)
                    ->setIp($ip)
                    ->setUserAgent($userAgent)
                    ->setSessionId($sessionId)
                    ->setUser($user)
                ;
            }

            $userBlockedAction->setExpiresAt($expiresAt);

            $em->persist($userBlockedAction);
            $em->flush();
        }

        return true;
    }
}
