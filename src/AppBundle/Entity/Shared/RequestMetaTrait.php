<?php

namespace AppBundle\Entity\Shared;

use AppBundle\Entity\User;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
trait RequestMetaTrait
{
    /**
     * @var string
     *
     * @ORM\Column(name="ip", type="string", length=255, nullable=true)
     */
    protected $ip;

    /**
     * @var string
     *
     * @ORM\Column(name="user_agent", type="text", nullable=true)
     */
    protected $userAgent;

    /**
     * @var string
     *
     * @ORM\Column(name="session_id", type="text", nullable=true)
     */
    protected $sessionId;

    /*** IP ***/

    /**
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @param $ip
     *
     * @return UserAction
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /*** User agent ***/

    /**
     * @return string
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * @param $userAgent
     *
     * @return UserAction
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    /*** Session ID ***/

    /**
     * @return string
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * @param $sessionId
     *
     * @return UserAction
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;

        return $this;
    }
}
