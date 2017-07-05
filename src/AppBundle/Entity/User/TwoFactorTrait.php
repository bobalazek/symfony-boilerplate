<?php

namespace AppBundle\Entity\User;

use AppBundle\Entity\User;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
trait TwoFactorTrait
{
    public static $twoFactorMethods = [
        'email' => 'Email',
        'google_authenticator' => 'Google Authenticator',
    ];

    /**
     * @var string
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="two_factor_method", type="string", length=64, nullable=true)
     */
    protected $twoFactorMethod;

    /*** Two Factor Method ***/

    /**
     * @return string
     */
    public function getTwoFactorMethod()
    {
        return $this->twoFactorMethod;
    }

    /**
     * @param $twoFactorMethod
     *
     * @return User
     */
    public function setTwoFactorMethod($twoFactorMethod)
    {
        $this->twoFactorMethod = $twoFactorMethod;

        return $this;
    }
}
