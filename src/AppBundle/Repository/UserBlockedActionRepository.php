<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class UserBlockedActionRepository extends EntityRepository
{
    /**
     * @param string $ip
     * @param string $sessionId
     * @param string $userAgent
     * @param string $type
     */
    public function getCurrentlyActive($ip, $sessionId, $userAgent, $action = 'login')
    {
        return $this->createQueryBuilder('uba')
            ->where(implode(' AND ', [
                'uba.ip = :ip',
                'uba.sessionId = :sessionId',
                'uba.userAgent = :userAgent',
                'uba.action = :action',
                'uba.expiresAt > :expiresAt',
                'uba.deletedAt is NULL',
            ]))
            ->orderBy('uba.expiresAt', 'DESC')
            ->setParameter('ip', $ip)
            ->setParameter('sessionId', $sessionId)
            ->setParameter('userAgent', $userAgent)
            ->setParameter('action', $action)
            ->setParameter('expiresAt', new \Datetime())
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
