<?php

namespace CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * User blocked action Entity.
 *
 * @Gedmo\Loggable
 * @ORM\Table(name="user_blocked_action")
 * @ORM\Entity(repositoryClass="CoreBundle\Repository\UserBlockedActionRepository")
 *
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class UserBlockedAction
{
    use ORMBehaviors\Blameable\Blameable,
        ORMBehaviors\Loggable\Loggable,
        ORMBehaviors\SoftDeletable\SoftDeletable,
        ORMBehaviors\Timestampable\Timestampable,
        Traits\Common\RequestMetaTrait
    ;

    public static $actions = [
        'login' => 'Login',
        'login.tfa' => '2FA Login',
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
     * @Gedmo\Versioned
     * @ORM\Column(name="action", type="string", length=32)
     */
    protected $action = 'login';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="expires_at", type="datetime", nullable=true)
     */
    protected $expiresAt;

    /**
     * @ORM\ManyToOne(targetEntity="CoreBundle\Entity\User", inversedBy="userBlockedActions")
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
     * @return UserBlockedAction
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /*** Action ***/

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param $action
     *
     * @return UserBlockedAction
     */
    public function setAction($action)
    {
        $this->action = $action;

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
     * @return UserBlockedAction
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
        $expiresAt = $this->getExpiresAt();

        if ($expiresAt === null) {
            return false;
        }

        return $expiresAt < new \Datetime();
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
     * @return UserBlockedAction
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
        return $this->getIp();
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->getId(),
            'action' => $this->getAction(),
            'is_expired' => $this->isExpired(),
            'expires_at' => $this->getExpiresAt()
                ? $this->getExpiresAt()->format(DATE_ATOM)
                : null,
            'created_at' => $this->getCreatedAt()->format(DATE_ATOM),
            'updated_at' => $this->getUpdatedAt()->format(DATE_ATOM),
        ];
    }
}
