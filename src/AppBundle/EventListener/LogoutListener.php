<?php

namespace AppBundle\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;
use AppBundle\Service\UserActionsService;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class LogoutListener implements LogoutHandlerInterface
{
    protected $userActionsService;

    public function __construct(UserActionsService $userActionsService)
    {
        $this->userActionsService = $userActionsService;
    }

    public function logout(Request $request, Response $response, TokenInterface $token)
    {
        $this->userActionsService->add(
            'user.logout',
            'User has logged out!'
        );
    }
}
