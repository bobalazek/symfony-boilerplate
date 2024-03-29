<?php

namespace CoreBundle\Entity\Traits\User;

use Gedmo\Mapping\Annotation as Gedmo;
use CoreBundle\Entity\User;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
trait RolesTrait
{
    public static $rolesAvailable = [
        'ROLE_SUPER_ADMIN' => 'Super admin',
        'ROLE_ADMIN' => 'Admin',
        'ROLE_USER' => 'User',
    ];

    /**
     * @var array
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="roles", type="json_array")
     */
    protected $roles = ['ROLE_USER'];

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
}
