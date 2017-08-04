<?php

namespace CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use CoreBundle\Entity\Profile;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class GenderType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'required' => false,
            'choices' => array_merge([
                '-- none --' => '',
            ], array_flip(Profile::$genders)),
        ]);
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
