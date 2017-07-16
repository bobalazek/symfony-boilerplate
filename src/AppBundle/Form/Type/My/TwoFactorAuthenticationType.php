<?php

namespace AppBundle\Form\Type\My;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use AppBundle\Form\Type\TwoFactorMethodType;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class TwoFactorAuthenticationType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('tfaEnabled', CheckboxType::class, [
                'required' => false,
                'label' => 'Enabled',
            ])
            ->add('tfaDefaultMethod', TwoFactorMethodType::class, [
                'label' => 'Default method',
            ])
            ->add('tfaEmailEnabled', CheckboxType::class, [
                'required' => false,
                'label' => 'Enable email authentication',
            ])
            ->add('tfaSmsEnabled', CheckboxType::class, [
                'required' => false,
                'label' => 'Enable SMS authentication',
            ])
            ->add('tfaAuthenticatorEnabled', CheckboxType::class, [
                'required' => false,
                'label' => 'Enable authenticator',
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\User',
            'validation_groups' => ['my.tfa'],
        ]);
    }
}
