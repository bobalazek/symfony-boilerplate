<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class UserActionRepository extends EntityRepository
{
    /**
     * @param string $ip
     * @param string $sessionId
     * @param string $userAgent
     * @param array  $bruteForceParameters
     */
    public function getFailedLoginAttemptsCount($ip, $sessionId, $userAgent, $bruteForceParameters)
    {
        $createdAt = (new \Datetime())->sub(
            new \Dateinterval('PT'.$bruteForceParameters['watch_time'].'S')
        );
        return $this->createQueryBuilder('ua')
            ->select('COUNT(ua.id)')
            ->where('ua.key = :key AND ua.createdAt > :createdAt')
            ->setParameter('key', 'user.login.fail')
            ->setParameter('createdAt', $createdAt)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
