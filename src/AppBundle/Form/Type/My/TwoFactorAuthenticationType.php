<?php

namespace AppBundle\Form\Type\My;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use AppBundle\Form\Type\TwoFactorMethodType as TwoFactorMethodTypeChoice;

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
                'label' => 'Enable email',
            ])
            ->add('twoFactorAuthenticationBackupCodesEnabled', CheckboxType::class, [
                'required' => false,
                'label' => 'Enable backup codes',
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
