<?php

namespace AppBundle\Form\Type\My;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class TwoFactorAuthenticationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('twoFactorAuthenticationEnabled', CheckboxType::class, [
                'required' => false,
                'label' => 'Enabled',
                'attr' => [
                    'data-help-text' => 'If you want to have the 2FA protection enabled. Note: it will only work, if you have on the the methods set up.',
                ],
            ])
            ->add('twoFactorAuthenticationEmailEnabled', CheckboxType::class, [
                'required' => false,
                'label' => 'Enable email authentication',
                'attr' => [
                    'data-help-text' => 'On each login, you will get an email with a security code, that you will need to type in, before you will be able to fully log into your account.',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\User',
            'validation_groups' => ['my.two_factor_authentication'],
        ]);
    }
}
