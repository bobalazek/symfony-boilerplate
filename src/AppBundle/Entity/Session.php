<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Session Entity.
 *
 * @ORM\Table(name="sessions")
 * @ORM\Entity
 *
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class Session
{
    /**
     * @var string
     *
     * @ORM\Column(name="sess_id", type="string", length=128)
     * @ORM\Id
     */
    protected $sessId;

    /**
     * @var string
     *
     * @ORM\Column(name="sess_data", type="blob")
     */
    protected $sessData;

    /**
     * @var string
     *
     * @ORM\Column(name="sess_time", type="integer")
     */
    protected $sessTime;

    /**
     * @var string
     *
     * @ORM\Column(name="sess_lifetime", type="integer")
     */
    protected $sessLifetime;
}
