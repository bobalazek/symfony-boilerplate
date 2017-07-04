<?php

namespace AppBundle\Entity\User;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
trait CodesTrait
{
    /**
     * @var string
     *
     * @ORM\Column(name="reset_password_code", type="string", length=255, nullable=true, unique=true)
     */
    protected $resetPasswordCode;

    /**
     * @var string
     *
     * @ORM\Column(name="activation_code", type="string", length=255, nullable=true, unique=true)
     */
    protected $activationCode;

    /**
     * @var string
     *
     * @ORM\Column(name="mobile_activation_code", type="string", length=255, nullable=true)
     */
    protected $mobileActivationCode;

    /**
     * @var string
     *
     * @ORM\Column(name="new_email_code", type="string", length=255, nullable=true, unique=true)
     */
    protected $newEmailCode;

    /*** Reset password code ***/

    /**
     * @return string
     */
    public function getResetPasswordCode()
    {
        return $this->resetPasswordCode;
    }

    /**
     * @param $resetPasswordCode
     *
     * @return User
     */
    public function setResetPasswordCode($resetPasswordCode)
    {
        $this->resetPasswordCode = $resetPasswordCode;

        return $this;
    }

    /*** Activation code ***/

    /**
     * @return string
     */
    public function getActivationCode()
    {
        return $this->activationCode;
    }

    /**
     * @param $activationCode
     *
     * @return User
     */
    public function setActivationCode($activationCode)
    {
        $this->activationCode = $activationCode;

        return $this;
    }

    /*** Mobile activation code ***/

    /**
     * @return string
     */
    public function getMobileActivationCode()
    {
        return $this->mobileActivationCode;
    }

    /**
     * @param $mobileActivationCode
     *
     * @return User
     */
    public function setMobileActivationCode($mobileActivationCode)
    {
        $this->mobileActivationCode = $mobileActivationCode;

        return $this;
    }

    /*** New email code ***/

    /**
     * @return string
     */
    public function getNewEmailCode()
    {
        return $this->newEmailCode;
    }

    /**
     * @param $newEmailCode
     *
     * @return User
     */
    public function setNewEmailCode($newEmailCode)
    {
        $this->newEmailCode = $newEmailCode;

        return $this;
    }
}
