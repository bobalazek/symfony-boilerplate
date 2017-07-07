<?php

namespace AppBundle\Form\Type\My;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Form\Type\TwoFactorMethodType as TwoFactorMethodTypeChoice;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class TwoFactorMethodType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('method', TwoFactorMethodTypeChoice::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\UserTwoFactorMethod',
            'validation_groups' => ['my.two_factor_authentication'],
        ]);
    }
}
