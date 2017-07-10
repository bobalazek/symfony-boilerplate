<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * User trusted device Entity.
 *
 * @ORM\Table(name="user_trusted_devices")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserTrustedDeviceRepository")
 *
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class UserTrustedDevice
{
    use ORMBehaviors\Blameable\Blameable,
        ORMBehaviors\Loggable\Loggable,
        ORMBehaviors\SoftDeletable\SoftDeletable,
        ORMBehaviors\Timestampable\Timestampable,
        Shared\RequestMetaTrait
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="text")
     */
    protected $token;

    /**
     * @var string
     *
     * @ORM\Column(name="data", type="json_array", nullable=true)
     */
    protected $data = [];

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_active_at", type="datetime", nullable=true)
     */
    protected $lastActiveAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="expires_at", type="datetime", nullable=true)
     */
    protected $expiresAt;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="userTrustedDevices")
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
     * @return UserTrustedDevice
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /*** Name ***/

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     *
     * @return UserTrustedDevice
     */
    public function setName($name)
    {
        $this->name = $name;

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
     * @param $token
     *
     * @return UserTrustedDevice
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /*** Data ***/

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     *
     * @return UserTrustedDevice
     */
    public function setData(array $data)
    {
        $this->data = $data;

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
     * @return User
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
        if ($this->getExpiresAt() === null) {
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
     * @return UserTrustedDevice
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
        return $this->getName();
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'token' => $this->getToken(),
            'ip' => $this->getIp(),
            'user_agent' => $this->getUserAgent(),
            'session_id' => $this->getSessionId(),
            'is_expired' => $this->isExpired(),
            'last_active_at' => $this->getLastActiveAt()
                ? $this->getLastActiveAt()->format(DATE_ATOM)
                : null,
            'expires_at' => $this->getExpiresAt()
                ? $this->getExpiresAt()->format(DATE_ATOM)
                : null,
            'created_at' => $this->getCreatedAt()->format(DATE_ATOM),
            'updated_at' => $this->getUpdatedAt()->format(DATE_ATOM),
        ];
    }
}
