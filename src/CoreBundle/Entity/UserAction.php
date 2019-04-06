<?php

namespace CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * User action Entity.
 *
 * @Gedmo\Loggable
 * @ORM\Table(name="user_actions")
 * @ORM\Entity(repositoryClass="CoreBundle\Repository\UserActionRepository")
 *
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class UserAction
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
     * @ORM\Column(name="`key`", type="text", nullable=true)
     */
    protected $key;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="text", nullable=true)
     */
    protected $message;

    /**
     * @var string
     *
     * @ORM\Column(name="data", type="json_array", nullable=true)
     */
    protected $data = [];

    /**
     * @ORM\ManyToOne(targetEntity="CoreBundle\Entity\User", inversedBy="userActions")
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
     * @return UserAction
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /*** Key ***/

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param $key
     *
     * @return UserAction
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /*** Message ***/

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param $message
     *
     * @return UserAction
     */
    public function setMessage($message)
    {
        $this->message = $message;

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
     * @return UserAction
     */
    public function setData(array $data)
    {
        $this->data = $data;

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
     * @return UserAction
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
        return '[' . $this->getKey() . '] ' . $this->getMessage();
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->getId(),
            'key' => $this->getKey(),
            'message' => $this->getMessage(),
            'ip' => $this->getIp(),
            'user_agent' => $this->getUserAgent(),
            'session_id' => $this->getSessionId(),
            'created_at' => $this->getCreatedAt()->format(DATE_ATOM),
            'updated_at' => $this->getUpdatedAt()->format(DATE_ATOM),
        ];
    }
}
