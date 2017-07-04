<?php

namespace AppBundle\Entity\User;

use AppBundle\Entity\User;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
trait TimestampsTrait
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_active_at", type="datetime", nullable=true)
     */
    protected $lastActiveAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="reset_password_code_expires_at", type="datetime", nullable=true)
     */
    protected $resetPasswordCodeExpiresAt;

    /**
     * When the email was activated at?
     *
     * @var \DateTime
     *
     * @ORM\Column(name="activated_at", type="datetime", nullable=true)
     */
    protected $activatedAt;

    /**
     * When the mobile number activated at?
     *
     * @var \DateTime
     *
     * @ORM\Column(name="mobile_activated_at", type="datetime", nullable=true)
     */
    protected $mobileActivatedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="mobile_activation_code_expires_at", type="datetime", nullable=true)
     */
    protected $mobileActivationCodeExpiresAt;

    /*** Last active at ***/

    /**
     * @return \DateTime
     */
    public function getLastActiveAt()
    {
        return $this->lastActiveAt;
    }

    /**
     * @param \DateTime $lastActiveAt
     *
     * @return User
     */
    public function setLastActiveAt(\DateTime $lastActiveAt = null)
    {
        $this->lastActiveAt = $lastActiveAt;

        return $this;
    }

    /*** Reset Password Code Expires at ***/

    /**
     * @return \DateTime
     */
    public function getResetPasswordCodeExpiresAt()
    {
        return $this->resetPasswordCodeExpiresAt;
    }

    /**
     * @param \DateTime $resetPasswordCodeExpiresAt
     *
     * @return User
     */
    public function setResetPasswordCodeExpiresAt(\DateTime $resetPasswordCodeExpiresAt = null)
    {
        $this->resetPasswordCodeExpiresAt = $resetPasswordCodeExpiresAt;

        return $this;
    }

    /*** Activated at ***/

    /**
     * @return \DateTime
     */
    public function getActivatedAt()
    {
        return $this->activatedAt;
    }

    /**
     * @param \DateTime $activatedAt
     *
     * @return User
     */
    public function setActivatedAt(\DateTime $activatedAt = null)
    {
        $this->activatedAt = $activatedAt;

        return $this;
    }

    /*** Mobile activated at ***/

    /**
     * @return \DateTime
     */
    public function getMobileActivatedAt()
    {
        return $this->mobileActivatedAt;
    }

    /**
     * @param \DateTime $mobileActivatedAt
     *
     * @return User
     */
    public function setMobileActivatedAt(\DateTime $mobileActivatedAt = null)
    {
        $this->mobileActivatedAt = $mobileActivatedAt;

        return $this;
    }

    /*** Mobile Activation Code Expires at ***/

    /**
     * @return \DateTime
     */
    public function getMobileActivationCodeExpiresAt()
    {
        return $this->mobileActivationCodeExpiresAt;
    }

    /**
     * @param \DateTime $mobileActivationCodeExpiresAt
     *
     * @return User
     */
    public function setMobileActivationCodeExpiresAt(\DateTime $mobileActivationCodeExpiresAt = null)
    {
        $this->mobileActivationCodeExpiresAt = $mobileActivationCodeExpiresAt;

        return $this;
    }
}
