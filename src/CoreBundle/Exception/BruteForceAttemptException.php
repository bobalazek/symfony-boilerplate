<?php

namespace CoreBundle\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class BruteForceAttemptException extends AuthenticationException
{
}
