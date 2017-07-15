<?php

namespace AppBundle\Entity\User;

use Gedmo\Mapping\Annotation as Gedmo;
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
     * @ORM\Column(name="new_email", type="string", length=255, nullable=true)
     */
    protected $newEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="new_email_code", type="string", length=255, nullable=true)
     */
    protected $newEmailCode;

    /**
     * @var string
     *
     * @ORM\Column(name="email_activation_code", type="string", length=255, nullable=true)
     */
    protected $emailActivationCode;

    /**
     * When the email was activated at?
     *
     * @var \DateTime
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="email_activated_at", type="datetime", nullable=true)
     */
    protected $emailActivatedAt;

    /**
     * @var string
     *
     * @ORM\Column(name="reset_password_code", type="string", length=255, nullable=true)
     */
    protected $resetPasswordCode;

    /**
     * @var \DateTime
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="reset_password_code_expires_at", type="datetime", nullable=true)
     */
    protected $resetPasswordCodeExpiresAt;

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

    /*** Email activation code ***/

    /**
     * @return string
     */
    public function getEmailActivationCode()
    {
        return $this->emailActivationCode;
    }

    /**
     * @param $emailActivationCode
     *
     * @return User
     */
    public function setEmailActivationCode($emailActivationCode)
    {
        $this->emailActivationCode = $emailActivationCode;

        return $this;
    }

    /*** Email activated at ***/

    /**
     * @return \DateTime
     */
    public function getEmailActivatedAt()
    {
        return $this->emailActivatedAt;
    }

    /**
     * @param \DateTime $emailActivatedAt
     *
     * @return User
     */
    public function setEmailActivatedAt(\DateTime $emailActivatedAt = null)
    {
        $this->emailActivatedAt = $emailActivatedAt;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEmailActivated()
    {
        return $this->getEmailActivatedAt() !== null;
    }

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
}
