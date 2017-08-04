<?php

namespace CoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use CoreBundle\Entity\User;
use CoreBundle\Entity\Profile;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class AppFixtures implements FixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        // Profile
        $profile = new Profile();
        $profile
            ->setFirstName('Borut')
            ->setLastName('Balazek')
        ;

        // User
        $user = new User();
        $user
            ->setUsername('bobalazek')
            ->setEmail('bobalazek124@gmail.com')
            ->setPlainPassword(
                'password',
                $this->container->get('security.password_encoder')
            )
            ->setRoles(['ROLE_SUPER_ADMIN'])
            ->setEmailActivatedAt(new \DateTime())
            ->setProfile($profile)
            ->enable()
            ->verify()
        ;

        $user->prepareUserRecoveryCodes(
            $this->getParameter('recovery_codes_count')
        );

        $manager->persist($user);
        $manager->flush();
    }
}
