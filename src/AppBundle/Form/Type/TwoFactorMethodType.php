<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class TwoFactorMethodType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'required' => false,
            'choices' => [
                '-- none --' => '',
                'Email' => 'email',
                'Google Authenticator' => 'google_authenticator',
            ],
        ]);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
