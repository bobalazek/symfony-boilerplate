<?php

namespace AppBundle\Entity\User;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
trait PasswordsTrait
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

        if ($encoder !== null) {
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
}
