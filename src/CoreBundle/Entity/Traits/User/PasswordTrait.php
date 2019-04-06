<?php

namespace CoreBundle\Entity\Traits\User;

use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use CoreBundle\Entity\User;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
trait PasswordTrait
{
    /**
     * @var string
     *
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="password", type="string", length=255)
     */
    protected $password;

    /**
     * Used only when saving the user.
     *
     * @var string
     *
     * @Assert\NotBlank(
     *     groups={"signup", "my.password", "reset_password"}
     * )
     */
    protected $plainPassword;

    /**
     * Used only when saving a new password.
     *
     * @var string
     *
     * @SecurityAssert\UserPassword(
     *     message="Wrong value for your current password",
     *     groups={"my.password"}
     * )
     */
    protected $oldPassword;

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

    /*** Password ***/

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        if (!empty($password)) {
            $this->password = $password;
        }

        return $this;
    }

    /*** Plain password ***/

    /**
     * @return string
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * @param $plainPassword
     * @param EncoderFactory $encoderFactory
     *
     * @return User
     */
    public function setPlainPassword($plainPassword, UserPasswordEncoder $encoder = null)
    {
        $this->plainPassword = $plainPassword;

        if (null !== $encoder) {
            $password = $encoder->encodePassword(
                $this,
                $plainPassword
            );

            $this->setPassword($password);
        }

        return $this;
    }

    /*** Old password ***/

    /**
     * @return string
     */
    public function getOldPassword()
    {
        return $this->oldPassword;
    }

    /**
     * @param $oldPassword
     *
     * @return User
     */
    public function setOldPassword($oldPassword)
    {
        $this->oldPassword = $oldPassword;

        return $this;
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

    /**
     * @return bool
     */
    public function isResetPasswordCodeExpired()
    {
        $expiresAt = $this->getResetPasswordCodeExpiresAt();

        if (null === $expiresAt) {
            return true;
        }

        return $expiresAt < new \Datetime();
    }
}
