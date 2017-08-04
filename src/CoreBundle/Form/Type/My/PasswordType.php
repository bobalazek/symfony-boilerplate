<?php

namespace CoreBundle\Form\Type\My;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType as SymfonyPasswordType;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class PasswordType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('oldPassword', SymfonyPasswordType::class)
            ->add('plainPassword', RepeatedType::class, [
                'type' => SymfonyPasswordType::class,
                'first_name' => 'newPassword',
                'second_name' => 'newPasswordRepeat',
                'invalid_message' => 'Password invalid.',
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'CoreBundle\Entity\User',
            'validation_groups' => ['my.password'],
        ]);
    }
}
