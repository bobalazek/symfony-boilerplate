<?php

namespace AppBundle\EventSubscriber;

use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\User;
use AppBundle\Entity\UserLoginBlock;
use AppBundle\Manager\UserActionManager;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class AuthenticationSubscriber implements EventSubscriberInterface
{
    protected $em;
    protected $userActionManager;
    protected $requestStack;
    protected $session;
    protected $bruteForceParameters;

    public function __construct(
        EntityManager $em,
        UserActionManager $userActionManager,
        RequestStack $requestStack,
        Session $session,
        array $bruteForceParameters
    ) {
        $this->em = $em;
        $this->userActionManager = $userActionManager;
        $this->requestStack = $requestStack;
        $this->session = $session;
        $this->bruteForceParameters = $bruteForceParameters;
    }

    /**
     * @param AuthenticationFailureEvent $event
     */
    public function onAuthenticationFailure(AuthenticationFailureEvent $event)
    {
        $authenticationTokenUser = $event->getAuthenticationToken()->getUser();

        $user = $this->em
            ->getRepository('AppBundle:User')
            ->findByUsernameOrEmail($authenticationTokenUser);

        $this->userActionManager->add(
            'user.login.fail',
            'User has tried to log in!',
            [
                'username' => $authenticationTokenUser,
            ],
            $user
        );

        $this->handleUserLoginBlocks(
            $this->requestStack->getCurrentRequest(),
            $this->session,
            $user
        );
    }

    /**
     * @param Request $request
     * @param Session $session
     *
     * @return bool
     */
    private function handleUserLoginBlocks(Request $request, Session $session, User $user = null)
    {
        $ip = $request->getClientIp();
        $sessionId = $session->getId();
        $userAgent = $request->headers->get('User-Agent');

        $attemptsCount = $this->em
            ->getRepository('AppBundle:UserAction')
            ->getFailedLoginAttemptsCount(
                $ip,
                $sessionId,
                $userAgent,
                $this->bruteForceParameters
            );

        if ($attemptsCount > $this->bruteForceParameters['max_attempts_before_block']) {
            $expiresAt = (new \Datetime())->add(
                new \Dateinterval('PT'.$this->bruteForceParameters['block_time'].'S')
            );

            $userLoginBlock = $this->em
                ->getRepository('AppBundle:UserLoginBlock')
                ->getCurrentlyActive(
                    $ip,
                    $sessionId,
                    $userAgent
                );

            if ($userLoginBlock === null) {
                $userLoginBlock = new UserLoginBlock();
                $userLoginBlock
                    ->setType('login')
                    ->setIp($ip)
                    ->setUserAgent($userAgent)
                    ->setSessionId($sessionId)
                    ->setUser($user)
                ;
            }

            $userLoginBlock->setExpiresAt($expiresAt);

            $this->em->persist($userLoginBlock);
            $this->em->flush();
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            AuthenticationEvents::AUTHENTICATION_FAILURE => ['onAuthenticationFailure'],
        ];
    }
}
