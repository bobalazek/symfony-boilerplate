<?php

namespace CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * User email login Entity.
 *
 * @Gedmo\Loggable
 * @ORM\Table(name="user_login_codes")
 * @ORM\Entity(repositoryClass="CoreBundle\Repository\UserLoginCodeRepository")
 *
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class UserLoginCode
{
    use ORMBehaviors\Blameable\Blameable,
        ORMBehaviors\Loggable\Loggable,
        ORMBehaviors\SoftDeletable\SoftDeletable,
        ORMBehaviors\Timestampable\Timestampable,
        Traits\Common\RequestMetaTrait
    ;

    public static $types = [
        'email' => 'Email',
        'sms' => 'SMS',
    ];

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
     * @ORM\Column(name="code", type="string", length=255, nullable=true)
     */
    protected $code;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=32, nullable=true)
     */
    protected $type = 'email';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="used_at", type="datetime", nullable=true)
     */
    protected $usedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="expires_at", type="datetime", nullable=true)
     */
    protected $expiresAt;

    /**
     * @ORM\ManyToOne(targetEntity="CoreBundle\Entity\User", inversedBy="userLoginCodes")
     */
    protected $user;

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
     * @return UserLoginCode
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /*** Code ***/

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param $code
     *
     * @return UserLoginCode
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /*** Type ***/

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $type
     *
     * @return UserLoginCode
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /*** Used at ***/

    /**
     * @return \DateTime
     */
    public function getUsedAt()
    {
        return $this->usedAt;
    }

    /**
     * @param \DateTime $usedAt
     *
     * @return UserLoginCode
     */
    public function setUsedAt(\DateTime $usedAt = null)
    {
        $this->usedAt = $usedAt;

        return $this;
    }

    /*** Expires at ***/

    /**
     * @return \DateTime
     */
    public function getExpiresAt()
    {
        return $this->expiresAt;
    }

    /**
     * @param \DateTime $expiresAt
     *
     * @return UserLoginCode
     */
    public function setExpiresAt(\DateTime $expiresAt = null)
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    /**
     * @return bool
     */
    public function isExpired()
    {
        if (null === $this->getExpiresAt()) {
            return false;
        }

        return $this->getExpiresAt() < new \Datetime();
    }

    /*** User ***/

    /**
     * @return User $user
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return UserLoginCode
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getCode();
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->getId(),
            'type' => $this->getType(),
            'code' => $this->getCode(),
            'ip' => $this->getIp(),
            'user_agent' => $this->getUserAgent(),
            'session_id' => $this->getSessionId(),
            'created_at' => $this->getCreatedAt()->format(DATE_ATOM),
            'updated_at' => $this->getUpdatedAt()->format(DATE_ATOM),
        ];
    }
}
