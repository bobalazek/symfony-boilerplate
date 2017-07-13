<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use AppBundle\Entity\User;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class TwoFactorMethodType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => array_flip(User::$tfaMethods),
        ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
