<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class UserLoginBlockRepository extends EntityRepository
{
    /**
     * @param string $ip
     * @param string $sessionId
     * @param string $userAgent
     */
    public function getCurrentlyActive($ip, $sessionId, $userAgent)
    {
        return $this->createQueryBuilder('ulb')
            ->where(implode(' AND ', [
                'ulb.ip = :ip',
                'ulb.sessionId = :sessionId',
                'ulb.userAgent = :userAgent',
                'ulb.expiresAt < :expiresAt',
                'ulb.deletedAt = :deletedAt',
            ]))
            ->orderBy('ulb.expiresAt', 'DESC')
            ->setParameter('ip', $ip)
            ->setParameter('sessionId', $sessionId)
            ->setParameter('userAgent', $userAgent)
            ->setParameter('expiresAt', new \Datetime())
            ->setParameter('deletedAt', null)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
