<?php

namespace AppBundle\Exception;

use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException as TooManyRequestsHttpExceptionBase;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class TooManyRequestsHttpException extends TooManyRequestsHttpExceptionBase
{
    /**
     * @param
     */
    public function __construct($message, $code) {
        parent::__construct(null, $message, null, $code);
    }
}
