<?php

namespace AppBundle\DataFixtures\Processor;

use AppBundle\Entity\User;
use Fidry\AliceDataFixtures\ProcessorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;

class UserProcessor implements ProcessorInterface
{
    /**
     * @var UserPasswordEncoder
     */
    private $passwordEncoder;

    /**
     * @param UserPasswordEncoder $passwordEncoder
     */
    public function __construct(UserPasswordEncoder $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * {@inheritdoc}
     */
    public function preProcess(string $id, $object)
    {
        if (!($object instanceof User)) {
            return;
        }

        if ($object->getPlainPassword()) {
            $object->setPlainPassword(
                $object->getPlainPassword(),
                $this->passwordEncoder
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function postProcess(string $id, $object)
    {
        // do nothing
    }
}
