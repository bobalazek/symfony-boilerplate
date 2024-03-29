<?php

namespace CoreBundle\Entity;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * User device Entity.
 *
 * @Gedmo\Loggable
 * @ORM\Table(name="user_devices")
 * @ORM\Entity(repositoryClass="CoreBundle\Repository\UserDeviceRepository")
 *
 * @UniqueEntity(
 *     fields={"uid"},
 *     errorPath="uid",
 *     message="This uid is already in use."
 * )
 *
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class UserDevice
{
    use ORMBehaviors\Blameable\Blameable,
        ORMBehaviors\Loggable\Loggable,
        ORMBehaviors\SoftDeletable\SoftDeletable,
        ORMBehaviors\Timestampable\Timestampable,
        Traits\Common\RequestMetaTrait
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
     * @ORM\Column(name="uid", type="string", length=255)
     */
    protected $uid;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="data", type="json_array", nullable=true)
     */
    protected $data = [];

    /**
     * @var bool
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="trusted", type="boolean")
     */
    protected $trusted = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_active_at", type="datetime", nullable=true)
     */
    protected $lastActiveAt;

    /**
     * @ORM\ManyToOne(targetEntity="CoreBundle\Entity\User", inversedBy="userDevices")
     */
    protected $user;

    /*** I ***/

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
     * @return UserDevice
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /*** UID ***/

    /**
     * @return string
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * @param $uid
     *
     * @return UserDevice
     */
    public function setUid($uid)
    {
        $this->uid = $uid;

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
     * @return UserDevice
     */
    public function setName($name)
    {
        $this->name = $name;

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
     * @return UserDevice
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /*** Trusted ***/

    /**
     * @return bool
     */
    public function isTrusted()
    {
        return $this->trusted;
    }

    /**
     * @param $trusted
     *
     * @return User
     */
    public function setTrusted($trusted)
    {
        $this->trusted = $trusted;

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
     * @return UserDevice
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
        $agent = $this->getUserAgentObject();

        return $agent->platform() . ' - ' . $agent->browser();
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'ip' => $this->getIp(),
            'user_agent' => $this->getUserAgent(),
            'session_id' => $this->getSessionId(),
            'last_active_at' => $this->getLastActiveAt()
                ? $this->getLastActiveAt()->format(DATE_ATOM)
                : null,
            'created_at' => $this->getCreatedAt()->format(DATE_ATOM),
            'updated_at' => $this->getUpdatedAt()->format(DATE_ATOM),
        ];
    }
}
