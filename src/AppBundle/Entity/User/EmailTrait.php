<?php

namespace AppBundle\Entity\User;

use AppBundle\Entity\User;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
trait EmailTrait
{
    /**
     * @var string
     *
     * @Gedmo\Versioned
     * @Assert\NotBlank(
     *     groups={"signup", "my.settings", "reset_password_request", "edit"}
     * )
     * @Assert\Email(
     *     groups={"signup", "my.settings", "reset_password_request", "edit"}
     * )
     *
     * @ORM\Column(name="email", type="string", length=128, unique=true)
     */
    protected $email;

    /**
     * We must confirm the new email, so temporary save it inside this field.
     *
     * @var string
     *
     * @Gedmo\Versioned
     * @Assert\Email(
     *     groups={"my.settings"}
     * )
     *
     * @ORM\Column(name="new_email", type="string", length=128, nullable=true)
     */
    protected $newEmail;

    /*** Email ***/

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /*** New email ***/

    /**
     * @return string
     */
    public function getNewEmail()
    {
        return $this->newEmail;
    }

    /**
     * @param $newEmail
     *
     * @return User
     */
    public function setNewEmail($newEmail)
    {
        $this->newEmail = $newEmail;

        return $this;
    }
}
