<?php

namespace AppBundle\Entity\User;

use Gedmo\Mapping\Annotation as Gedmo;
use AppBundle\Entity\User;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
trait TwoFactorAuthenticationTrait
{
    public static $tfaMethods = [
        'email' => 'Email',
        'sms' => 'SMS',
        'authenticator' => 'Authenticator',
        'recovery_code' => 'Recovery code',
    ];

    /**
     * Get all the possible two-factor authentication methods.
     *
     * @return bool
     */
    public function getAvailableTFAMethods()
    {
        $availableMethods = self::$tfaMethods;

        // Email
        $isTFAEmailEnabled = $this->isTFAEmailEnabled();
        $isEmailActivated = $this->isEmailActivated();
        if (
            !$isTFAEmailEnabled ||
            !$isEmailActivated
        ) {
            unset($availableMethods['email']);
        }

        // SMS
        $isTFASmsEnabled = $this->isTFASmsEnabled();
        $isMobileActivated = $this->isMobileActivated();
        if (
            !$isTFASmsEnabled ||
            !$isMobileActivated
        ) {
            unset($availableMethods['sms']);
        }

        // Authenticator
        $isTFAAuthenticatorEnabled = $this->isTFAAuthenticatorEnabled();
        $isTFAAuthenticatorActivated = $this->isTFAAuthenticatorActivated();
        $tfaAuthenticatorSecret = $this->getTFAAuthenticatorSecret();
        if (
            !$isTFAAuthenticatorEnabled ||
            !$isTFAAuthenticatorActivated ||
            empty($tfaAuthenticatorSecret)
        ) {
            unset($availableMethods['authenticator']);
        }

        // Recovery code
        $recoveryCodes = $this->getUserRecoveryCodes(true, true);
        if (empty($recoveryCodes)) {
            unset($availableMethods['recovery_code']);
        }

        return $availableMethods;
    }

    /**
     * @var bool
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="tfa_enabled", type="boolean")
     */
    protected $tfaEnabled = false;

    /**
     * @var bool
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="tfa_default_method", type="string", length=32, nullable=true)
     */
    protected $tfaDefaultMethod;

    /**
     * @var bool
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="tfa_email_enabled", type="boolean")
     */
    protected $tfaEmailEnabled = false;

    /**
     * @var bool
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="tfa_sms_enabled", type="boolean")
     */
    protected $tfaSmsEnabled = false;

    /**
     * @var bool
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="tfa_authenticator_enabled", type="boolean")
     */
    protected $tfaAuthenticatorEnabled = false;

    /**
     * @var bool
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="tfa_authenticator_secret", type="string", length=255, nullable=true)
     */
    protected $tfaAuthenticatorSecret;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="tfa_authenticator_activated_at", type="datetime", nullable=true)
     */
    protected $tfaAuthenticatorActivatedAt;

    /*** TFA Enabled ***/

    /**
     * @return bool
     */
    public function isTFAEnabled()
    {
        return $this->tfaEnabled;
    }

    /**
     * @param $tfaEnabled
     *
     * @return User
     */
    public function setTFAEnabled($tfaEnabled)
    {
        $this->tfaEnabled = $tfaEnabled;

        return $this;
    }

    /**
     * @return User
     */
    public function enableTFA()
    {
        $this->setTFAEnabled(true);

        return $this;
    }

    /**
     * @return User
     */
    public function disableTFA()
    {
        $this->setTFAEnabled(false);

        return $this;
    }

    /*** TFA default method ***/

    /**
     * @return string
     */
    public function getTFADefaultMethod()
    {
        return $this->tfaDefaultMethod;
    }

    /**
     * @param $tfaDefaultMethod
     *
     * @return User
     */
    public function setTFADefaultMethod($tfaDefaultMethod)
    {
        $this->tfaDefaultMethod = $tfaDefaultMethod;

        return $this;
    }

    /*** TFA email enabled ***/

    /**
     * @return bool
     */
    public function isTFAEmailEnabled()
    {
        return $this->tfaEmailEnabled;
    }

    /**
     * @param $tfaEmailEnabled
     *
     * @return User
     */
    public function setTFAEmailEnabled($tfaEmailEnabled)
    {
        $this->tfaEmailEnabled = $tfaEmailEnabled;

        return $this;
    }

    /**
     * @return User
     */
    public function enableTFAEmail()
    {
        $this->setTFAEmailEnabled(true);

        return $this;
    }

    /**
     * @return User
     */
    public function disableTFAEmail()
    {
        $this->setTFAEmailEnabled(false);

        return $this;
    }

    /*** TFA SMS enabled ***/

    /**
     * @return bool
     */
    public function isTFASmsEnabled()
    {
        return $this->tfaSmsEnabled;
    }

    /**
     * @param $tfaSmsEnabled
     *
     * @return User
     */
    public function setTFASmsEnabled($tfaSmsEnabled)
    {
        $this->tfaSmsEnabled = $tfaSmsEnabled;

        return $this;
    }

    /**
     * @return User
     */
    public function enableTFASms()
    {
        $this->setTFASmsEnabled(true);

        return $this;
    }

    /**
     * @return User
     */
    public function disableTFASms()
    {
        $this->setTFASmsEnabled(false);

        return $this;
    }

    /*** TFA authenticator enabled ***/

    /**
     * @return bool
     */
    public function isTFAAuthenticatorEnabled()
    {
        return $this->tfaAuthenticatorEnabled;
    }

    /**
     * @param $tfaAuthenticatorEnabled
     *
     * @return User
     */
    public function setTFAAuthenticatorEnabled($tfaAuthenticatorEnabled)
    {
        $this->tfaAuthenticatorEnabled = $tfaAuthenticatorEnabled;

        return $this;
    }

    /**
     * @return User
     */
    public function enableTFAAuthenticator()
    {
        $this->setTFAAuthenticatorEnabled(true);

        return $this;
    }

    /**
     * @return User
     */
    public function disableTFAAuthenticator()
    {
        $this->setTFAAuthenticatorEnabled(false);

        return $this;
    }

    /*** TFA authenticator secret ***/

    /**
     * @return string
     */
    public function getTFAAuthenticatorSecret()
    {
        return $this->tfaAuthenticatorSecret;
    }

    /**
     * @param $tfaAuthenticatorSecret
     *
     * @return User
     */
    public function setTFAAuthenticatorSecret($tfaAuthenticatorSecret)
    {
        $this->tfaAuthenticatorSecret = $tfaAuthenticatorSecret;

        return $this;
    }

    /*** TFA Authenticator activated at ***/

    /**
     * @return \DateTime
     */
    public function getTFAAuthenticatorActivatedAt()
    {
        return $this->tfaAuthenticatorActivatedAt;
    }

    /**
     * @param \DateTime $tfaAuthenticatorActivatedAt
     *
     * @return User
     */
    public function setTFAAuthenticatorActivatedAt(
        \DateTime $tfaAuthenticatorActivatedAt = null
    ) {
        $this->tfaAuthenticatorActivatedAt = $tfaAuthenticatorActivatedAt;

        return $this;
    }

    /**
     * @return bool
     */
    public function isTFAAuthenticatorActivated()
    {
        return $this->getTFAAuthenticatorActivatedAt() !== null;
    }
}
