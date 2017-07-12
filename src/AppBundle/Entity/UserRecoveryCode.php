<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * User recovery code Entity.
 *
 * @Gedmo\Loggable
 * @ORM\Table(name="user_recovery_codes")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRecoveryCodeRepository")
 *
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class UserRecoveryCode
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
     * @ORM\Column(name="code", type="string", length=16)
     */
    protected $code;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="used_at", type="datetime", nullable=true)
     */
    protected $usedAt;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="userRecoveryCodes")
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
     * @return UserRecoveryCode
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
     * @return UserRecoveryCode
     */
    public function setCode($code)
    {
        $this->code = $code;

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
     * @return UserRecoveryCode
     */
    public function setUsedAt(\DateTime $usedAt = null)
    {
        $this->usedAt = $usedAt;

        return $this;
    }

    /**
     * @return bool
     */
    public function isUsed()
    {
        return $this->getUsedAt() !== null;
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
     * @return UserRecoveryCode
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
        return (string) $this->getCode();
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->getId(),
            'code' => $this->getCode(),
            'is_used' => $this->getUsedAt() !== null,
            'used_at' => $this->getUsedAt()
                ? $this->getUsedAt()->format(DATE_ATOM)
                : null,
            'created_at' => $this->getCreatedAt()->format(DATE_ATOM),
            'updated_at' => $this->getUpdatedAt()->format(DATE_ATOM),
        ];
    }
}
