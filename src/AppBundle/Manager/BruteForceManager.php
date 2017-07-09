<?php

namespace AppBundle\Manager;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Exception\BruteForceAttemptException;

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

        $userLoginBlock = $em->getRepository('AppBundle:UserLoginBlock')
            ->getCurrentlyActive(
                $ip,
                $sessionId,
                $userAgent
            );
        if ($userLoginBlock) {
            throw new BruteForceAttemptException(
                $this->container->get('translator')->trans(
                    'Your account has been blocked from logging it. The block will be released at %time%',
                    [
                        '%time%' => $userLoginBlock->getExpiresAt()->format($dateTimeFormat),
                    ]
                )
            );
        }

        return true;
    }
}
