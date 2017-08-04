<?php

namespace CoreBundle\Form\Type;

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
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TitleType::class, [
                'label' => 'Title',
            ])
            ->add('firstName', TextType::class, [
                'label' => 'First name',
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Last name',
            ])
            ->add('gender', GenderType::class, [
                'label' => 'Gender',
            ])
            ->add('birthday', BirthdayType::class, [
                'required' => false,
                'label' => 'Birthday',
                'years' => range(
                    date('Y'),
                    date('Y') - 120
                ),
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'CoreBundle\Entity\Profile',
            'validation_groups' => ['signup', 'my.settings'],
            'cascade_validation' => true,
        ]);
    }
}
