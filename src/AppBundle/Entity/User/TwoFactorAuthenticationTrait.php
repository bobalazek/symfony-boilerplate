<?php

namespace AppBundle\Entity\User;

use AppBundle\Entity\User;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
trait TwoFactorAuthenticationTrait
{
    public static $twoFactorAuthenticationMethods = [
        'email' => 'Email',
    ];

    /**
     * @var bool
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="two_factor_authentication_enabled", type="boolean")
     */
    protected $twoFactorAuthenticationEnabled = false;

    /**
     * @var bool
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="two_factor_authentication_default_method", type="string", length=64, nullable=true)
     */
    protected $twoFactorAuthenticationDefaultMethod = 'email';

    /**
     * @var bool
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="two_factor_authentication_email_enabled", type="boolean")
     */
    protected $twoFactorAuthenticationEmailEnabled = false;

    /*** Two factor authentication Enabled ***/

    /**
     * @return bool
     */
    public function isTwoFactorAuthenticationEnabled()
    {
        return $this->twoFactorAuthenticationEnabled;
    }

    /**
     * @param $twoFactorAuthenticationEnabled
     *
     * @return User
     */
    public function setTwoFactorAuthenticationEnabled($twoFactorAuthenticationEnabled)
    {
        $this->twoFactorAuthenticationEnabled = $twoFactorAuthenticationEnabled;

        return $this;
    }

    /**
     * @return User
     */
    public function enableTwoFactorAuthentication()
    {
        $this->setTwoFactorAuthenticationEnabled(true);

        return $this;
    }

    /**
     * @return User
     */
    public function disableTwoFactorAuthentication()
    {
        $this->setTwoFactorAuthenticationEnabled(false);

        return $this;
    }

    /*** Two factor authentication default method ***/

    /**
     * @return string
     */
    public function getTwoFactorAuthenticationDefaultMethod()
    {
        return $this->twoFactorAuthenticationDefaultMethod;
    }

    /**
     * @param $twoFactorAuthenticationDefaultMethod
     *
     * @return User
     */
    public function setTwoFactorAuthenticationDefaultMethod($twoFactorAuthenticationDefaultMethod)
    {
        $this->twoFactorAuthenticationDefaultMethod = $twoFactorAuthenticationDefaultMethod;

        return $this;
    }

    /*** Two factor authentication email enabled ***/

    /**
     * @return bool
     */
    public function isTwoFactorAuthenticationEmailEnabled()
    {
        return $this->twoFactorAuthenticationEmailEnabled;
    }

    /**
     * @param $twoFactorAuthenticationEmailEnabled
     *
     * @return User
     */
    public function setTwoFactorAuthenticationEmailEnabled($twoFactorAuthenticationEmailEnabled)
    {
        $this->twoFactorAuthenticationEmailEnabled = $twoFactorAuthenticationEmailEnabled;

        return $this;
    }

    /**
     * @return User
     */
    public function enableTwoFactorAuthenticationEmail()
    {
        $this->setTwoFactorAuthenticationEmailEnabled(true);

        return $this;
    }

    /**
     * @return User
     */
    public function disableTwoFactorAuthenticationEmail()
    {
        $this->setTwoFactorAuthenticationEmailEnabled(false);

        return $this;
    }
}
