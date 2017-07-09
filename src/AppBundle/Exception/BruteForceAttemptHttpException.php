<?php

namespace AppBundle\Exception;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class BruteForceAttemptHttpException extends AccessDeniedHttpException
{
}
