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
                'attr' => [
                    'data-help-text' => 'If you want to have the 2FA protection enabled. Note: it will only work, if you have on the the methods set up.',
                ],
            ])
            ->add('tfaDefaultMethod', TwoFactorMethodType::class, [
                'label' => 'Default method',
                'attr' => [
                    'data-help-text' => 'Which is the default method on the 2FA?',
                ],
            ])
            ->add('tfaEmailEnabled', CheckboxType::class, [
                'required' => false,
                'label' => 'Enable email authentication',
                'attr' => [
                    'data-help-text' => 'On each login, you will get an email with a security code, that you will need to type in, before you will be able to fully log into your account.',
                ],
            ])
            ->add('tfaAuthenticatorEnabled', CheckboxType::class, [
                'required' => false,
                'label' => 'Enable authenticator',
                'attr' => [
                    'data-help-text' => 'On each login, you will need to enter the code on your authenticator (Google Authenticator, Authy, ...).',
                ],
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
