<?php

namespace AppBundle\Manager;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Exception\BruteForceAttemptException;
use AppBundle\Entity\User;
use AppBundle\Entity\UserBlockedAction;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class BruteForceManager
{
    use ContainerAwareTrait;

    /**
     * @return bool
     */
    public function attemptAuthentication(Request $request)
    {
        $session = $this->container->get('session');
        $em = $this->container->get('doctrine.orm.entity_manager');

        $dateTimeFormat = $this->container->getParameter('date_time_format');
        $ip = $request->getClientIp();
        $sessionId = $session->getId();
        $userAgent = $request->headers->get('User-Agent');

        $userBlockedAction = $em->getRepository('AppBundle:UserBlockedAction')
            ->getCurrentlyActive(
                $ip,
                $sessionId,
                $userAgent,
                'login'
            );
        if ($userBlockedAction) {
            throw new BruteForceAttemptException(
                $this->container->get('translator')->trans(
                    'Your account has been blocked from logging in. The block will be released at %time%.',
                    [
                        '%time%' => $userBlockedAction->getExpiresAt()->format($dateTimeFormat),
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
    public function handleUserLoginBlocks(
        User $user = null,
        $action = 'login',
        $userActionKey = 'user.login.fail'
    ) {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $session = $this->container->get('session');
        $bruteForceParameters = $this->container->getParameter('brute_force');

        $ip = $request->getClientIp();
        $sessionId = $session->getId();
        $userAgent = $request->headers->get('User-Agent');

        $attemptsCount = $em->getRepository('AppBundle:UserAction')
            ->getFailedLoginAttemptsCount(
                $ip,
                $sessionId,
                $userAgent,
                $userActionKey,
                $bruteForceParameters['watch_time']
            );

        if ($attemptsCount > $bruteForceParameters['max_attempts_before_block']) {
            $expiresAt = (new \Datetime())->add(
                new \Dateinterval('PT'.$bruteForceParameters['block_time'].'S')
            );

            $userBlockedAction = $em->getRepository('AppBundle:UserBlockedAction')
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
