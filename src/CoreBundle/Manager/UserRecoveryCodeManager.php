<?php

namespace CoreBundle\Manager;

use Symfony\Component\DependencyInjection\ContainerInterface;
use CoreBundle\Entity\User;
use CoreBundle\Entity\UserRecoveryCode;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class UserRecoveryCodeManager
{
    /** @var ContainerInterface */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $code
     * @param User   $user
     *
     * @return UserRecoveryCode
     */
    public function get($code, User $user)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $userRecoveryCode = $em->getRepository('CoreBundle:UserRecoveryCode')
            ->findOneBy([
                'code' => $code,
                'user' => $user,
            ]);
        if (
            null !== $userRecoveryCode &&
            false === $userRecoveryCode->isUsed() &&
            false === $userRecoveryCode->isDeleted()
        ) {
            return $userRecoveryCode;
        }

        return null;
    }

    /**
     * If there is any valid user recovery code.
     *
     * @param string $code
     * @param User   $user
     *
     * @return bool
     */
    public function exists($code, User $user)
    {
        return null !== $this->get($code, $user);
    }
}
