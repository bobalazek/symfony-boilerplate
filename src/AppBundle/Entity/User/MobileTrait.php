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
}
