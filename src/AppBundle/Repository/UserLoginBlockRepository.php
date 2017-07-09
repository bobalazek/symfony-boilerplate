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
     * @param Request $request
     * @param Session $session
     */
    public function getCurrentlyActive(Request $request, Session $session)
    {
        return $this->createQueryBuilder('ulg')
            ->where('ulb.ip = :ip AND ulb.sessionId = :sessionid AND ulb.userAgent = :userAgent AND ulb.expiresAt < :expiresAt AND ulb.deletedAt = :deletedAt')
            ->orderBy('expiresAt', 'DESC')
            ->setParameter('ip', $request->getClientIp())
            ->setParameter('sessionId', $session->getId())
            ->setParameter('ip', $request->headers->get('User-Agent'))
            ->setParameter('expiresAt', new \Datetime())
            ->setParameter('deletedAt', null)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
