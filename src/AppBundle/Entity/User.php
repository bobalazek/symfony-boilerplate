<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Criteria;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * User Entity.
 *
 * @Gedmo\Loggable
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 * @UniqueEntity(
 *     fields={"email"},
 *     errorPath="email",
 *     message="This email is already in use.",
 *     groups={"signup", "my.settings", "edit"}
 * )
 * @UniqueEntity(
 *     fields={"username"},
 *     errorPath="username",
 *     message="This username is already in use.",
 *     groups={"signup", "my.settings", "edit"}
 * )
 *
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class User implements AdvancedUserInterface, \Serializable
{
    use ORMBehaviors\Blameable\Blameable,
        ORMBehaviors\Loggable\Loggable,
        ORMBehaviors\SoftDeletable\SoftDeletable,
        ORMBehaviors\Timestampable\Timestampable
    ;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="username", type="string", length=64, unique=true)
     */
    protected $username;

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
     * We must confirm the new password, so temporary save it inside this field.
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
     * @var array
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="roles", type="json_array")
     */
    protected $roles = ['ROLE_USER'];

    /**
     * @var string
     *
     * @ORM\Column(name="salt", type="string", length=255, nullable=true)
     */
    protected $salt;

    /**
     * @ORM\Column(name="token", type="string", length=255, nullable=true)
     */
    protected $token;

    /**
     * @var bool
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="enabled", type="boolean")
     */
    protected $enabled = false;

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
     * @ORM\Column(name="warned", type="boolean")
     */
    protected $warned = false;

    /**
     * @var bool
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="locked", type="boolean")
     */
    protected $locked = false;

    /**
     * @var string
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="locked_reason", type="text", nullable=true)
     */
    protected $lockedReason;

    /**
     * @var bool
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="newsletter", type="boolean")
     */
    protected $newsletter = false;

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
     * @ORM\Column(name="new_email_code", type="string", length=255, nullable=true, unique=true)
     */
    protected $newEmailCode;

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
     * @Assert\Valid
     * @Assert\NotBlank()
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Profile", mappedBy="user", cascade={"all"})
     **/
    protected $profile;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\UserAction", mappedBy="user")
     */
    protected $userActions;

    /**
     * Otherwise known as: userExpired / accountExpired.
     *
     * @var bool
     */
    protected $expired = false;

    /**
     * @var bool
     */
    protected $credentialsExpired = false;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->profile = new Profile();
        $this->profile->setUser($this);

        $this->setSalt(
            md5(uniqid(null, true))
        );

        $this->setToken(
            md5(uniqid(null, true))
        );

        $this->setActivationCode(
            md5(uniqid(null, true))
        );

        $this->userActions = new ArrayCollection();
    }

    /*** Id ***/

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id
     *
     * @return User
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /*** Username ***/

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param $username
     *
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

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

    /*** Roles ***/

    /**
     * @return array
     */
    public function getRoles()
    {
        $roles = is_array($this->roles)
            ? $this->roles
            : []
        ;

        $roles[] = 'ROLE_USER';

        return (array) array_unique($roles, SORT_REGULAR);
    }

    /**
     * @param array $roles
     *
     * @return User
     */
    public function setRoles(array $roles = ['ROLE_USER'])
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @param string $role
     *
     * @return bool
     */
    public function addRole($role)
    {
        $this->roles[] = $role;

        $this->roles = (array) array_unique($this->roles, SORT_REGULAR);

        return $this;
    }

    /**
     * @param string $role
     *
     * @return bool
     */
    public function hasRole($role)
    {
        return in_array(
            $role,
            $this->getRoles()
        );
    }

    /**
     * @return bool
     */
    public function isAdmin()
    {
        return $this->hasRole('ROLE_ADMIN') || $this->isSuperAdmin();
    }

    /**
     * @return bool
     */
    public function isSuperAdmin()
    {
        return $this->hasRole('ROLE_SUPER_ADMIN');
    }

    /*** Salt ***/

    /**
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * @param $salt
     *
     * @return User
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /*** Token ***/

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     *
     * @return User
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /*** Enabled ***/

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param $enabled
     *
     * @return User
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return User
     */
    public function enable()
    {
        $this->setEnabled(true);

        return $this;
    }

    /**
     * @return User
     */
    public function disable()
    {
        $this->setEnabled(false);

        return $this;
    }

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

    /*** Warned ***/

    /**
     * @return bool
     */
    public function isWarned()
    {
        return $this->warned;
    }

    /**
     * @param $warned
     *
     * @return User
     */
    public function setWarned($warned)
    {
        $this->warned = $warned;

        return $this;
    }

    /**
     * @return User
     */
    public function warn()
    {
        $this->setWarned(true);

        return $this;
    }

    /**
     * @return User
     */
    public function unwarn()
    {
        $this->setWarned(false);

        return $this;
    }

    /*** Locked ***/

    /**
     * @return bool
     */
    public function isLocked()
    {
        return $this->locked;
    }

    /**
     * @param $locked
     *
     * @return User
     */
    public function setLocked($locked)
    {
        $this->locked = $locked;

        return $this;
    }

    /**
     * @param $reason
     *
     * @return User
     */
    public function lock($reason = '')
    {
        $this->setLocked(true);
        $this->setLockedReason($reason);

        return $this;
    }
    
    /**
     * @return User
     */
    public function unlock()
    {
        $this->setLocked(false);
        $this->setLockedReason(null);

        return $this;
    }

    /**
     * @return bool
     */
    public function isAccountNonLocked()
    {
        return !$this->isLocked();
    }

    /*** Locked reason ***/

    /**
     * @return string
     */
    public function getLockedReason()
    {
        return $this->lockedReason;
    }

    /**
     * @param $lockedReason
     *
     * @return User
     */
    public function setLockedReason($lockedReason)
    {
        $this->lockedReason = $lockedReason;

        return $this;
    }

    /*** Newsletter ***/

    /**
     * @return bool
     */
    public function getNewsletter()
    {
        return $this->newsletter;
    }

    /**
     * Just an alias for the isNewsletter method.
     *
     * @return bool
     */
    public function hasNewsletter()
    {
        return $this->getNewsletter();
    }

    /**
     * @param $newsletter
     *
     * @return User
     */
    public function setNewsletter($newsletter)
    {
        $this->newsletter = $newsletter;

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
    public function getResetPasswordCodeExpires()
    {
        return $this->resetPasswordCodeExpiresAt;
    }

    /**
     * @param \DateTime $resetPasswordCodeExpiresAt
     *
     * @return User
     */
    public function setPasswordCodeExpires(\DateTime $resetPasswordCodeExpiresAt = null)
    {
        $this->resetPasswordCodeExpiresAt = $resetPasswordCodeExpiresAt;

        return $this;
    }

    /*** Expired ***/

    /**
     * @return bool
     */
    public function getExpired()
    {
        return $this->expired;
    }

    /**
     * @return bool
     */
    public function isExpired()
    {
        return $this->getExpired();
    }

    /**
     * @return bool
     */
    public function isAccountNonExpired()
    {
        return !$this->getExpired();
    }

    /*** Credentials expired ***/

    /**
     * @return bool
     */
    public function getCredentialsExpired()
    {
        return $this->credentialsExpired;
    }

    /**
     * @return bool
     */
    public function isCredentialsExpired()
    {
        return $this->getCredentialsExpired();
    }

    /**
     * @return bool
     */
    public function isCredentialsNonExpired()
    {
        return !$this->getExpired();
    }

    /*** Profile ***/

    /**
     * @return Profile
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * @param Profile $profile
     *
     * @return User
     */
    public function setProfile(Profile $profile)
    {
        $this->profile = $profile;

        $this->getProfile()->setUser($this);

        return $this;
    }

    /*** User actions ***/

    /**
     * @return array
     */
    public function getUserActions()
    {
        $criteria = Criteria::create()->orderBy([
            'createdAt' => Criteria::DESC,
        ]);

        return $this->userActions->matching($criteria)->toArray();
    }

    /**
     * @param $userActions
     *
     * @return User
     */
    public function setUserActions($userActions)
    {
        $this->userActions = $userActions;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEqualTo(AdvancedUserInterface $user)
    {
        if (!($user instanceof AdvancedUserInterface)) {
            return false;
        }

        if ($this->getPassword() !== $user->getPassword()) {
            return false;
        }

        if ($this->getSalt() !== $user->getSalt()) {
            return false;
        }

        if ($this->getUsername() !== $user->getUsername()) {
            return false;
        }

        if (serialize($this->getRoles()) !== serialize($user->getRoles())) {
            return false;
        }

        return true;
    }

    public function eraseCredentials()
    {
        $this->setPlainPassword(null);
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize([
            $this->id,
            $this->username,
            $this->email,
            $this->password,
            $this->salt,
        ]);
    }

    public function unserialize($serialized)
    {
        list(
            $this->id,
            $this->username,
            $this->email,
            $this->password,
            $this->salt
        ) = unserialize($serialized);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName().' ('.$this->getUsername().')';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getProfile()
            ? $this->getProfile()->getFullName()
            : 'Unknown';
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->getId(),
            'username' => $this->getUsername(),
            'email' => $this->getEmail(),
            'token' => $this->getToken(),
            'super_admin' => $this->isSuperAdmin(),
            'admin' => $this->isAdmin(),
            'profile' => $this->getProfile()->toArray(),
            'created_at' => $this->getCreatedAt()->format(DATE_ATOM),
            'updated_at' => $this->getUpdatedAt()->format(DATE_ATOM),
        ];
    }
}
