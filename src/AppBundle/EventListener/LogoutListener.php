<?php

namespace AppBundle\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;
use AppBundle\Manager\UserActionManager;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class LogoutListener implements LogoutHandlerInterface
{
    protected $userActionManager;

    public function __construct(UserActionManager $userActionManager)
    {
        $this->userActionManager = $userActionManager;
    }

    public function logout(Request $request, Response $response, TokenInterface $token)
    {
        $this->userActionManager->add(
            'user.logout',
            'User has logged out!'
        );
    }
}
