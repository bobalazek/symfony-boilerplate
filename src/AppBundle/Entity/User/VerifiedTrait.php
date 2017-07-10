<?php

namespace AppBundle\Entity\User;

use Gedmo\Mapping\Annotation as Gedmo;
use AppBundle\Entity\User;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
trait VerifiedTrait
{
    /**
     * @var bool
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="verified", type="boolean")
     */
    protected $verified = false;

    /**
     * @var bool
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="email_verified", type="boolean")
     */
    protected $emailVerified = false;

    /**
     * @var bool
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="mobile_verified", type="boolean")
     */
    protected $mobileVerified = false;

    /*** Verified ***/

    /**
     * @return bool
     */
    public function isVerified()
    {
        return $this->verified;
    }

    /**
     * @param $verified
     *
     * @return User
     */
    public function setVerified($verified)
    {
        $this->verified = $verified;

        return $this;
    }

    /**
     * @return User
     */
    public function verify()
    {
        $this->setVerified(true);

        return $this;
    }

    /**
     * @return User
     */
    public function unverify()
    {
        $this->setVerified(false);

        return $this;
    }

    /*** Email verified ***/

    /**
     * @return bool
     */
    public function isEmailVerified()
    {
        return $this->emailVerified;
    }

    /**
     * @param $emailVerified
     *
     * @return User
     */
    public function setEmailVerified($emailVerified)
    {
        $this->emailVerified = $emailVerified;

        return $this;
    }

    /**
     * @return User
     */
    public function verifyEmail()
    {
        $this->setEmailVerified(true);

        return $this;
    }

    /**
     * @return User
     */
    public function unverifyEmail()
    {
        $this->setEmailVerified(false);

        return $this;
    }

    /*** Mobile verified ***/

    /**
     * @return bool
     */
    public function isMobileVerified()
    {
        return $this->mobileVerified;
    }

    /**
     * @param $mobileVerified
     *
     * @return User
     */
    public function setMobileVerified($mobileVerified)
    {
        $this->mobileVerified = $mobileVerified;

        return $this;
    }

    /**
     * @return User
     */
    public function verifyMobile()
    {
        $this->setMobileVerified(true);

        return $this;
    }

    /**
     * @return User
     */
    public function unverifyMobile()
    {
        $this->setMobileVerified(false);

        return $this;
    }
}
