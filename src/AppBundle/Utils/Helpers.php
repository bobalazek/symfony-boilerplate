<?php

namespace AppBundle\Utils;

class Helpers {
    /**
     * Creates the cookie for that trusted device.
     *
     * @param int $length
     *
     * @return string
     */
    public static function getRandomString($length = 16)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $string = '';

        for ($i = 0; $i < $length; ++$i) {
            $string .= $characters[mt_rand(0, strlen($characters) - 1)];
        }

        return $string;
    }
}
