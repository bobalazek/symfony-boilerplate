<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class UserRepository extends EntityRepository
{
    /**
     * @param string $username
     */
    public function findByUsernameOrEmail($username)
    {
        $user = $this->createQueryBuilder('u')
            ->where('u.username = :username OR u.email = :username')
            ->setParameter('username', $username)
            ->getQuery()
            ->getOneOrNullResult();

        if ($user && $user->isDeleted()) {
            return null;
        }

        return $user;
    }

    /**
     * @param string $id
     * @param string $token
     */
    public function findByIdAndToken($id, $token)
    {
        $user = $this->createQueryBuilder('u')
            ->where('(u.id = :id AND u.token = :token) AND u.deletedAt = :deleted')
            ->setParameter('id', $id)
            ->setParameter('token', $token)
            ->setParameter('deleted', null)
            ->getQuery()
            ->getOneOrNullResult();

        if ($user && $user->isDeleted()) {
            return null;
        }

        return $user;
    }
}
