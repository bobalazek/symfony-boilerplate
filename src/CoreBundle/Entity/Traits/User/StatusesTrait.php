<?php

namespace CoreBundle\Entity\Traits\User;

use Gedmo\Mapping\Annotation as Gedmo;
use CoreBundle\Entity\User;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
trait StatusesTrait
{
    /**
     * @var bool
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="enabled", type="boolean")
     */
    protected $enabled = false;

    /**
     * @var bool
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="warned", type="boolean")
     */
    protected $warned = false;

    /**
     * @var string
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="warned_reason", type="text", nullable=true)
     */
    protected $warnedReason;

    /**
     * @var bool
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="locked", type="boolean")
     */
    protected $locked = false;

    /**
     * @var string
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="locked_reason", type="text", nullable=true)
     */
    protected $lockedReason;

    /**
     * @var bool
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="newsletter", type="boolean")
     */
    protected $newsletter = false;

    /**
     * @var bool
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="verified", type="boolean")
     */
    protected $verified = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_active_at", type="datetime", nullable=true)
     */
    protected $lastActiveAt;

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

    /*** Warned ***/

    /**
     * @return bool
     */
    public function isWarned()
    {
        return $this->warned;
    }

    /**
     * @param $warned
     *
     * @return User
     */
    public function setWarned($warned)
    {
        $this->warned = $warned;

        return $this;
    }

    /*** Warned reason ***/

    /**
     * @return string
     */
    public function getWarnedReason()
    {
        return $this->warnedReason;
    }

    /**
     * @param $warnedReason
     *
     * @return User
     */
    public function setWarnedReason($warnedReason)
    {
        $this->warnedReason = $warnedReason;

        return $this;
    }

    /**
     * @param $reason
     *
     * @return User
     */
    public function warn($reason)
    {
        $this->setWarned(true);
        $this->setWarnedReason($reason);

        return $this;
    }

    /**
     * @return User
     */
    public function unwarn()
    {
        $this->setWarned(false);
        $this->setLockedReason(null);

        return $this;
    }

    /*** Locked ***/

    /**
     * @return bool
     */
    public function isLocked()
    {
        return $this->locked;
    }

    /**
     * @param $locked
     *
     * @return User
     */
    public function setLocked($locked)
    {
        $this->locked = $locked;

        return $this;
    }

    /**
     * @param $reason
     *
     * @return User
     */
    public function lock($reason = '')
    {
        $this->setLocked(true);
        $this->setLockedReason($reason);

        return $this;
    }

    /**
     * @return User
     */
    public function unlock()
    {
        $this->setLocked(false);
        $this->setLockedReason(null);

        return $this;
    }

    /**
     * @return bool
     */
    public function isAccountNonLocked()
    {
        return false === $this->isLocked();
    }

    /*** Locked reason ***/

    /**
     * @return string
     */
    public function getLockedReason()
    {
        return $this->lockedReason;
    }

    /**
     * @param $lockedReason
     *
     * @return User
     */
    public function setLockedReason($lockedReason)
    {
        $this->lockedReason = $lockedReason;

        return $this;
    }

    /*** Newsletter ***/

    /**
     * @return bool
     */
    public function getNewsletter()
    {
        return $this->newsletter;
    }

    /**
     * Just an alias for the isNewsletter method.
     *
     * @return bool
     */
    public function hasNewsletter()
    {
        return $this->getNewsletter();
    }

    /**
     * @param $newsletter
     *
     * @return User
     */
    public function setNewsletter($newsletter)
    {
        $this->newsletter = $newsletter;

        return $this;
    }

    /*** Verified ***/

    /**
     * @return bool
     */
    public function isVerified()
    {
        return $this->verified;
    }

    /**
     * @param $verified
     *
     * @return User
     */
    public function setVerified($verified)
    {
        $this->verified = $verified;

        return $this;
    }

    /**
     * @return User
     */
    public function verify()
    {
        $this->setVerified(true);

        return $this;
    }

    /**
     * @return User
     */
    public function unverify()
    {
        $this->setVerified(false);

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
}
