<?php

namespace AppBundle\Form\Type\My;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use AppBundle\Form\Type\ProfileType;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class SettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('profile', ProfileType::class, [
                'label' => false,
                'by_reference' => true,
                'data_class' => 'AppBundle\Entity\Profile',
                'validation_groups' => ['my.settings'],
            ])
            ->add('username', TextType::class)
            ->add('email', EmailType::class)
            ->add('locale', LocaleType::class)
            ->add('mobile', PhoneNumberType::class, [
                'required' => false,
                'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('authorization_checker');
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\User',
            'validation_groups' => ['my.settings'],
            'cascade_validation' => true,
        ]);
    }
}
