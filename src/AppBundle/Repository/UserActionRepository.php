<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class UserActionRepository extends EntityRepository
{
    /**
     * @param string $ip
     * @param string $sessionId
     * @param string $userAgent
     * @param string $key
     * @param int    $watchTime For how long back do we track the login attempts?
     */
    public function getFailedLoginAttemptsCount($ip, $sessionId, $userAgent, $key, $watchTime)
    {
        $createdAt = (new \Datetime())->sub(
            new \Dateinterval('PT'.$watchTime.'S')
        );

        return $this->createQueryBuilder('ua')
            ->select('COUNT(ua.id)')
            ->where('ua.key = :key AND ua.createdAt > :createdAt')
            ->setParameter('key', $key)
            ->setParameter('createdAt', $createdAt)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
