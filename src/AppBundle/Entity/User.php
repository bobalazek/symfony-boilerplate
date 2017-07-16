<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Criteria;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use AppBundle\Utils\Helpers;

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
        ORMBehaviors\Timestampable\Timestampable,
        User\StatusesTrait,
        User\TwoFactorAuthenticationTrait,
        User\RolesTrait,
        User\PasswordsTrait,
        User\EmailTrait,
        User\MobileTrait
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
     * @ORM\Column(name="salt", type="string", length=255, nullable=true)
     */
    protected $salt;

    /**
     * @ORM\Column(name="token", type="string", length=255, nullable=true)
     */
    protected $token;

    /**
     * @Assert\Locale()
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="locale", type="string", length=32, nullable=true)
     */
    protected $locale = 'en_US';

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
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\UserAction", mappedBy="user", cascade={"all"})
     */
    protected $userActions;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\UserRecoveryCode", mappedBy="user", cascade={"all"})
     */
    protected $userRecoveryCodes;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\UserDevice", mappedBy="user", cascade={"all"})
     */
    protected $userDevices;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\UserLoginCode", mappedBy="user", cascade={"all"})
     */
    protected $userLoginCodes;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\UserBlockedAction", mappedBy="user", cascade={"all"})
     */
    protected $userBlockedActions;

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

        $this->userActions = new ArrayCollection();
        $this->userRecoveryCodes = new ArrayCollection();
        $this->userDevices = new ArrayCollection();
        $this->userLoginCodes = new ArrayCollection();
        $this->userBlockedActions = new ArrayCollection();
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

    /*** Locale ***/

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     *
     * @return User
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
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

    /*** User recovery codes ***/

    /**
     * @param bool $onlyNonDeleted Show only active/non-deleted two-factor methods
     * @param bool $onlyNonUsed    Show only active/non-used backup codes
     *
     * @return array
     */
    public function getUserRecoveryCodes(
        $onlyNonDeleted = false,
        $onlyNonUsed = false
    ) {
        $criteria = Criteria::create();

        if ($onlyNonDeleted) {
            $criteria->where(Criteria::expr()->eq(
                'deletedAt',
                null
            ));
        }

        if ($onlyNonUsed) {
            $criteria->where(Criteria::expr()->eq(
                'usedAt',
                null
            ));
        }

        $criteria->orderBy([
            'createdAt' => Criteria::DESC,
        ]);

        return $this->userRecoveryCodes->matching($criteria)->toArray();
    }

    /**
     * @param $userRecoveryCodes
     *
     * @return User
     */
    public function setUserRecoveryCodes($userRecoveryCodes)
    {
        $this->userRecoveryCodes = $userRecoveryCodes;

        return $this;
    }

    /**
     * @param int $count
     *
     * @return User
     */
    public function prepareUserRecoveryCodes($count = 8)
    {
        for ($i = 0; $i < $count; ++$i) {
            $code = Helpers::getRandomString(8).'-'.Helpers::getRandomString(8);
            $userRecoveryCode = new UserRecoveryCode();
            $userRecoveryCode
                ->setCode($code)
                ->setUser($this)
            ;

            $this->userRecoveryCodes->add($userRecoveryCode);
        }
    }

    /*** User devices ***/

    /**
     * @param bool $onlyNonDeleted Show only active/non-deleted two-factor methods
     *
     * @return array
     */
    public function getUserDevices($onlyNonDeleted = false)
    {
        $criteria = Criteria::create()->orderBy([
            'createdAt' => Criteria::DESC,
        ]);

        if ($onlyNonDeleted) {
            $criteria->where(Criteria::expr()->eq(
                'deletedAt',
                null
            ));
        }

        return $this->userDevices->matching($criteria)->toArray();
    }

    /**
     * @param $userDevices
     *
     * @return User
     */
    public function setUserDevices($userDevices)
    {
        $this->userDevices = $userDevices;

        return $this;
    }

    /*** User login codes ***/

    /**
     * @return array
     */
    public function getUserLoginCodes()
    {
        $criteria = Criteria::create()->orderBy([
            'createdAt' => Criteria::DESC,
        ]);

        return $this->userLoginCodes->matching($criteria)->toArray();
    }

    /**
     * @param $userLoginCodes
     *
     * @return User
     */
    public function setUserLoginCodes($userLoginCodes)
    {
        $this->userLoginCodes = $userLoginCodes;

        return $this;
    }

    /*** User blocked actions ***/

    /**
     * @return array
     */
    public function getUserBlockedActions()
    {
        $criteria = Criteria::create()->orderBy([
            'createdAt' => Criteria::DESC,
        ]);

        return $this->userBlockedActions->matching($criteria)->toArray();
    }

    /**
     * @param $userBlockedActio
     *
     * @return User
     */
    public function setUserBlockedActions($userBlockedActions)
    {
        $this->userBlockedActions = $userBlockedActions;

        return $this;
    }

    /*** Expired ***/

    /**
     * @return bool
     */
    public function isExpired()
    {
        return $this->expired;
    }

    /**
     * @return bool
     */
    public function isAccountNonExpired()
    {
        return !$this->isExpired();
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
        return !$this->isExpired();
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
            'name' => $this->getName(),
            'username' => $this->getUsername(),
            'email' => $this->getEmail(),
            'mobile' => $this->getMobile(),
            'token' => $this->getToken(),
            'locale' => $this->getLocale(),
            'is_enabled' => $this->isEnabled(),
            'is_verified' => $this->isVerified(),
            'is_warned' => $this->isWarned(),
            'warned_reason' => $this->getWarnedReason(),
            'is_locked' => $this->isLocked(),
            'locked_reason' => $this->getLockedReason(),
            'is_super_admin' => $this->isSuperAdmin(),
            'is_admin' => $this->isAdmin(),
            'profile' => $this->getProfile()->toArray(),
            'created_at' => $this->getCreatedAt()->format(DATE_ATOM),
            'updated_at' => $this->getUpdatedAt()->format(DATE_ATOM),
        ];
    }
}
