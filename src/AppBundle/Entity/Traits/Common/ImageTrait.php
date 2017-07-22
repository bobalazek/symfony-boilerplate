<?php

namespace AppBundle\Entity\Traits\Common;

use Doctrine\ORM\Mapping as ORM;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
trait ImageTrait
{
    /**
     * @var string
     *
     * @ORM\Column(name="image_url", type="text", nullable=true)
     */
    protected $imageUrl;

    /*** Image URL ***/

    /**
     * @return string
     */
    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    /**
     * @param $imageUrl
     *
     * @return $this
     */
    public function setImageUrl($imageUrl)
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }
}
