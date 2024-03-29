<?php

namespace CoreBundle\Form\Type\My;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use CoreBundle\Form\Type\ProfileType;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class SettingsType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('profile', ProfileType::class, [
                'label' => false,
                'by_reference' => true,
                'data_class' => 'CoreBundle\Entity\Profile',
                'validation_groups' => ['my.settings'],
            ])
            ->add('username', TextType::class, [
                'label' => 'Username',
            ])
            ->add('locale', LocaleType::class, [
                'label' => 'Locale',
                'choices' => array_flip($options['locales']),
                'choice_loader' => null,
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
            ])
            ->add('mobile', PhoneNumberType::class, [
                'required' => false,
                'label' => 'Mobile',
                'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
                'preferred_country_choices' => ['DE', 'AT', 'CH'],
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['locales']);
        $resolver->setDefaults([
            'data_class' => 'CoreBundle\Entity\User',
            'validation_groups' => ['my.settings'],
            'cascade_validation' => true,
        ]);
    }
}
