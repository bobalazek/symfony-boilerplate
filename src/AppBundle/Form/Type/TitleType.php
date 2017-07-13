<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use AppBundle\Entity\Profile;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class TitleType extends AbstractType
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
            ], array_flip(Profile::$titles)),
        ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
