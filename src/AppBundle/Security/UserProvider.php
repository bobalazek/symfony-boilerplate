<?php

namespace AppBundle\Security;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\User;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class UserProvider implements UserProviderInterface
{
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function loadUserByUsername($username)
    {
        $user = $this->em
            ->getRepository('AppBundle:User')
            ->findByUsernameOrEmail($username);

        if ($user) {
            return $user;
        }

        throw new UsernameNotFoundException(
            sprintf('Username "%s" does not exist.', $username)
        );
    }

    public function loadUserByIdAndToken($id, $token)
    {
        $user = $this->em
            ->getRepository('AppBundle:User')
            ->findByIdAndToken($id, $token);

        if ($user) {
            return $user;
        }

        throw new UsernameNotFoundException(
            sprintf('An user with the id "%s" token "%s" does not exist.', $id, $token)
        );
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * @return bool
     */
    public function supportsClass($class)
    {
        return User::class === $class;
    }
}
