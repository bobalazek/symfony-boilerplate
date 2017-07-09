<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class UserLoginBlockRepository extends EntityRepository
{
    /**
     * @param string $ip
     * @param string $sessionId
     * @param string $userAgent
     * @param string $type
     */
    public function getCurrentlyActive($ip, $sessionId, $userAgent, $type = 'login')
    {
        return $this->createQueryBuilder('ulb')
            ->where(implode(' AND ', [
                'ulb.ip = :ip',
                'ulb.sessionId = :sessionId',
                'ulb.userAgent = :userAgent',
                'ulb.type = :type',
                'ulb.expiresAt > :expiresAt',
                'ulb.deletedAt is NULL',
            ]))
            ->orderBy('ulb.expiresAt', 'DESC')
            ->setParameter('ip', $ip)
            ->setParameter('sessionId', $sessionId)
            ->setParameter('userAgent', $userAgent)
            ->setParameter('type', $type)
            ->setParameter('expiresAt', new \Datetime())
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
