<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TitleType::class)
            ->add('firstName', TextType::class)
            ->add('lastName', TextType::class)
            ->add('gender', GenderType::class)
            ->add('birthday', BirthdayType::class, [
                'required' => false,
                'years' => range(
                    date('Y'),
                    date('Y') - 120
                ),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\Profile',
            'validation_groups' => ['signup', 'my.settings'],
            'cascade_validation' => true,
        ]);
    }
}
