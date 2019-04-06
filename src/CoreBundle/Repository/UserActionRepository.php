<?php

namespace CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class UserActionRepository extends EntityRepository
{
    /**
     * Get the number of certain actions in a certain time frame until now.
     *
     * @param string $ip
     * @param string $sessionId
     * @param string $userAgent
     * @param string $key
     * @param int    $watchTime For how long back do we track the login attempts?
     */
    public function getCount($key, $watchTime, $ip, $sessionId, $userAgent)
    {
        $createdAt = (new \Datetime())->sub(
            new \Dateinterval('PT' . $watchTime . 'S')
        );

        return $this->createQueryBuilder('ua')
            ->select('COUNT(ua.id)')
            ->where(implode(' AND ', [
                'ua.key = :key',
                'ua.ip = :ip',
                'ua.createdAt = :createdAt',
            ]))
            ->setParameter('key', $key)
            ->setParameter('ip', $ip)
            ->setParameter('createdAt', $createdAt)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
