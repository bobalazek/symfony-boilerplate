<?php

namespace AppBundle\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;
use Symfony\Component\Translation\DataCollectorTranslator;
use AppBundle\Manager\UserActionManager;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class LogoutListener implements LogoutHandlerInterface
{
    protected $userActionManager;
    protected $translator;

    /**
     * @param UserActionManager       $userActionManager
     * @param DataCollectorTranslator $translator
     */
    public function __construct(UserActionManager $userActionManager, DataCollectorTranslator $translator)
    {
        $this->userActionManager = $userActionManager;
        $this->translator = $translator;
    }

    /**
     * @param Request        $request
     * @param Response       $response
     * @param TokenInterface $token
     */
    public function logout(Request $request, Response $response, TokenInterface $token)
    {
        $this->userActionManager->add(
            'user.logout',
            $this->translator->trans(
                'logout.user_action.text'
            )
        );
    }
}
