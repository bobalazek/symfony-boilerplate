<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class UserRepository extends EntityRepository
{
    public function findByUsernameOrEmail($username)
    {
        return $this->createQueryBuilder('u')
            ->where('u.username = :username OR u.email = :username')
            ->setParameter('username', $username)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByIdAndToken($id, $token)
    {
        return $this->createQueryBuilder('u')
            ->where('u.id = :id AND u.token = :token')
            ->setParameter('id', $id)
            ->setParameter('token', $token)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
