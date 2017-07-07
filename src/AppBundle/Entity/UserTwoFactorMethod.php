<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * User two factor method Entity.
 *
 * @Gedmo\Loggable
 * @ORM\Table(name="user_two_factor_methods")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserTwoFactorMethodRepository")
 *
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class UserTwoFactorMethod
{
    use ORMBehaviors\Blameable\Blameable,
        ORMBehaviors\Loggable\Loggable,
        ORMBehaviors\SoftDeletable\SoftDeletable,
        ORMBehaviors\Timestampable\Timestampable
    ;

    public static $methods = [
        'email' => 'Email',
        'google_authenticator' => 'Google Authenticator',
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
     * @ORM\Column(name="method", type="string", length=64, nullable=true)
     */
    protected $method;

    /**
     * @var string
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="data", type="json_array", nullable=true)
     */
    protected $data = [];

    /**
     * @var bool
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="enabled", type="boolean")
     */
    protected $enabled = true;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="userTwoFactorMethods")
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

    /*** Method ***/

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param $method
     *
     * @return UserAction
     */
    public function setMethod($method)
    {
        $this->method = $method;

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
        return $this->getMethod();
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->getId(),
            'method' => $this->getKey(),
            'data' => $this->getData(),
            'is_enabled' => $this->isEnabled(),
            'created_at' => $this->getCreatedAt()->format(DATE_ATOM),
            'updated_at' => $this->getUpdatedAt()->format(DATE_ATOM),
        ];
    }
}
