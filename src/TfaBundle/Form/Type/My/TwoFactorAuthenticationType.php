<?php

namespace TfaBundle\Form\Type\My;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use CoreBundle\Entity\User;

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
        $defaultMethodChoices = array_merge(
            ['-- none --' => null],
            array_flip(User::$tfaMethods)
        );
        $availableMethods = $options['user']->getAvailableTFAMethods();
        $builder
            ->add('tfaEnabled', CheckboxType::class, [
                'required' => false,
                'label' => 'Enabled',
            ])
            ->add('tfaDefaultMethod', ChoiceType::class, [
                'label' => 'Default method',
                'choices' => $defaultMethodChoices,
                'choice_attr' => function ($val, $key, $index) use ($availableMethods) {
                    return !in_array($key, $availableMethods) && $val !== null
                        ? ['disabled' => 'disabled']
                        : [];
                },
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
        $resolver->setRequired('user');
        $resolver->setDefaults([
            'data_class' => 'CoreBundle\Entity\User',
            'validation_groups' => ['my.tfa'],
        ]);
    }
}
