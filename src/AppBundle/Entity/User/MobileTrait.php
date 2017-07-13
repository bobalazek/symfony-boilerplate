<?php

namespace AppBundle\Entity\User;

use Gedmo\Mapping\Annotation as Gedmo;
use AppBundle\Entity\User;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
trait MobileTrait
{
    /**
     * @var string
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="mobile", type="phone_number", nullable=true)
     */
    protected $mobile;

    /**
     * We must confirm the new mobile, so temporary save it inside this field.
     *
     * @var string
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="new_mobile", type="phone_number", nullable=true)
     */
    protected $newMobile;

    /**
     * @var string
     *
     * @ORM\Column(name="mobile_activation_code", type="string", length=255, nullable=true)
     */
    protected $mobileActivationCode;

    /**
     * When the mobile number activated at?
     *
     * @var \DateTime
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="mobile_activated_at", type="datetime", nullable=true)
     */
    protected $mobileActivatedAt;

    /**
     * @var \DateTime
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="mobile_activation_code_expires_at", type="datetime", nullable=true)
     */
    protected $mobileActivationCodeExpiresAt;

    /*** Mobile ***/

    /**
     * @return string
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * @param $mobile
     *
     * @return User
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;

        return $this;
    }

    /*** New mobile ***/

    /**
     * @return string
     */
    public function getNewMobile()
    {
        return $this->newMobile;
    }

    /**
     * @param $newMobile
     *
     * @return User
     */
    public function setNewMobile($newMobile)
    {
        $this->newMobile = $newMobile;

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

    /**
     * @return bool
     */
    public function isMobileActivated()
    {
        return $this->getMobileActivatedAt() !== null;
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
