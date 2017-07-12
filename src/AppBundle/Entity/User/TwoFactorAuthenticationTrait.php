<?php

namespace AppBundle\Entity\User;

use Gedmo\Mapping\Annotation as Gedmo;
use AppBundle\Entity\User;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
trait TwoFactorAuthenticationTrait
{
    public static $twoFactorAuthenticationMethods = [
        'email' => 'Email',
        // 'sms' => 'SMS', // not available for now
        'authenticator' => 'Authenticator',
        'recovery_code' => 'Recovery code',
    ];

    /**
     * Get all the possible two-factor authentication methods.
     *
     * @return bool
     */
    public function getAvailableTwoFactorAuthenticationMethods()
    {
        $availableMethods = self::$twoFactorAuthenticationMethods;

        // Email
        $isEmailEmailEnabled = $user->isTwoFactorAuthenticationEmailEnabled();
        if (!$isEmailEmailEnabled) {
            unset($availableMethods['email']);
        }

        // Two-factor authenticator
        // TODO

        // Recovery code
        $userRecoveryCodes = $this->getUserRecoveryCodes();
        if (empty($userRecoveryCodes)) {
            unset($availableMethods['recovery_code']);
        }

        return $availableMethods;
    }

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
     * @ORM\Column(name="two_factor_authentication_default_method", type="string", length=32, nullable=true)
     */
    protected $twoFactorAuthenticationDefaultMethod = 'email';

    /**
     * @var bool
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="two_factor_authentication_email_enabled", type="boolean")
     */
    protected $twoFactorAuthenticationEmailEnabled = false;

    /**
     * @var bool
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="two_factor_authentication_authenticator_enabled", type="boolean")
     */
    protected $twoFactorAuthenticationAuthenticatorEnabled = false;

    /**
     * @var bool
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="two_factor_authentication_authenticator_secret", type="string", length=255, nullable=true)
     */
    protected $twoFactorAuthenticationAuthenticatorSecret;

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

    /*** Two factor authentication authenticator enabled ***/

    /**
     * @return bool
     */
    public function isTwoFactorAuthenticationAuthenticatorEnabled()
    {
        return $this->twoFactorAuthenticationAuthenticatorEnabled;
    }

    /**
     * @param $twoFactorAuthenticationAuthenticatorEnabled
     *
     * @return User
     */
    public function setTwoFactorAuthenticationAuthenticatorEnabled($twoFactorAuthenticationAuthenticatorEnabled)
    {
        $this->twoFactorAuthenticationAuthenticatorEnabled = $twoFactorAuthenticationAuthenticatorEnabled;

        return $this;
    }

    /**
     * @return User
     */
    public function enableTwoFactorAuthenticationAuthenticator()
    {
        $this->setTwoFactorAuthenticationAuthenticatorEnabled(true);

        return $this;
    }

    /**
     * @return User
     */
    public function disableTwoFactorAuthenticationAuthenticator()
    {
        $this->setTwoFactorAuthenticationAuthenticatorEnabled(false);

        return $this;
    }

    /*** Two factor authentication authenticator secret ***/

    /**
     * @return string
     */
    public function getTwoFactorAuthenticationAuthenticatorSecret()
    {
        return $this->twoFactorAuthenticationAuthenticatorSecret;
    }

    /**
     * @param $twoFactorAuthenticationAuthenticatorSecret
     *
     * @return User
     */
    public function setTwoFactorAuthenticationAuthenticatorSecret($twoFactorAuthenticationAuthenticatorSecret)
    {
        $this->twoFactorAuthenticationAuthenticatorSecret = $twoFactorAuthenticationAuthenticatorSecret;

        return $this;
    }
}
