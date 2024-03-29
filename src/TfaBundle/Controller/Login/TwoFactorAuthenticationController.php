<?php

namespace TfaBundle\Controller\Login;

use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use CoreBundle\Exception\BruteForceAttemptException;
use CoreBundle\Entity\User;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class TwoFactorAuthenticationController extends Controller
{
    /**
     * @Route("/login/tfa", name="login.tfa")
     */
    public function tfaAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session = $this->get('session');
        $tfaInProgress = $session->get(
            'two_factor_authentication_in_progress'
        );

        if (
            null === $tfaInProgress ||
            false === $tfaInProgress
        ) {
            $this->addFlash(
                'info',
                $this->get('translator')->trans(
                    'general.already_logged_in'
                )
            );

            return $this->redirectToRoute('home');
        }

        $user = $this->getUser();
        $method = $session->get(
            'two_factor_authentication_method'
        );
        $alternativeMethods = $this->getAlternativeMethods(
            $user,
            $method
        );

        // Check if the user has switched the 2FA method
        $methodSwitchResponse = $this->handleMethodSwitch(
            $user,
            $request,
            $session,
            $method,
            $alternativeMethods
        );
        if ($methodSwitchResponse) {
            return $methodSwitchResponse;
        }

        if ('POST' === $request->getMethod()) {
            $code = $request->request->get('code');
            $isTrustedDevice = 'yes' === $request->request->get('is_trusted_device');

            $postResponse = $this->handlePost(
                $method,
                $code,
                $isTrustedDevice,
                $user,
                $request,
                $session,
                $em
            );
            if ($postResponse) {
                return $postResponse;
            }
        }

        return $this->render(
            'TfaBundle:Content:login/tfa.html.twig',
            [
                'method' => $method,
                'alternative_methods' => $alternativeMethods,
                'code' => $request->query->get('code'),
            ]
        );
    }

    /***** Handle post methods *****/

    /**
     * @param string                 $method
     * @param string                 $code
     * @param bool                   $isTrustedDevice
     * @param User                   $user
     * @param Request                $request
     * @param Session                $session
     * @param EntityManagerInterface $em
     *
     * @return Response|null
     */
    private function handlePost(
        $method,
        $code,
        $isTrustedDevice,
        $user,
        Request $request,
        Session $session,
        EntityManagerInterface $em
    ) {
        try {
            $this->handleTooManyAttempts(
                $request,
                $session,
                $em
            );
        } catch (BruteForceAttemptException $e) {
            $this->addFlash(
                'danger',
                $e->getMessage()
            );

            return $this->redirectToRoute(
                'login.tfa'
            );
        }

        $success = false;
        if (
            'email' === $method ||
            'sms' === $method
        ) {
            $success = $this->handlePostLoginCode(
                $method,
                $code,
                $user,
                $request,
                $session,
                $em
            );
        } elseif ('authenticator' === $method) {
            $success = $this->handlePostAuthenticator(
                $code,
                $user,
                $request,
                $session,
                $em
            );
        } elseif ('recovery_code' === $method) {
            $success = $this->handlePostRecoveryCode(
                $code,
                $user,
                $request,
                $session,
                $em
            );
        }

        if (false === $success) {
            $this->addFlash(
                'danger',
                $this->get('translator')->trans(
                    'login.tfa.code_invalid.text'
                )
            );

            $this->handleFailedLoginAttempt(
                $user,
                $method,
                $code
            );

            return null;
        }

        if ($isTrustedDevice) {
            $this->get('app.user_device_manager')
                ->setCurrentAsTrusted(
                    $user,
                    $request
                );
        }

        $response = $this->redirectToRoute('home');

        $this->addFlash(
            'success',
            $this->get('translator')->trans(
                'login.tfa.success'
            )
        );

        $session->remove(
            'two_factor_authentication_in_progress'
        );

        $this->get('app.user_action_manager')
            ->add(
                'user.login.tfa',
                $this->container->get('translator')->trans(
                    'login.tfa.user_action.text'
                )
            );

        return $response;
    }

    /**
     * Handle the post request, if it is the email method.
     * We use the same function for email & sms 2FA methods.
     *
     * @param string                 $method
     * @param string                 $code
     * @param User                   $user
     * @param Request                $request
     * @param Session                $session
     * @param EntityManagerInterface $em
     *
     * @return bool Was it successfull?
     */
    private function handlePostLoginCode(
        $method,
        $code,
        User $user,
        Request $request,
        Session $session,
        EntityManagerInterface $em
    ) {
        return $this
            ->get('app.user_login_code_manager')
            ->exists($code, $user);
    }

    /**
     * Handle when a user tries to login with an authenticator.
     *
     * @param string                 $code
     * @param User                   $user
     * @param Request                $request
     * @param Session                $session
     * @param EntityManagerInterface $em
     *
     * @return bool Was it successfull?
     */
    private function handlePostAuthenticator(
        $code,
        User $user,
        Request $request,
        Session $session,
        EntityManagerInterface $em
    ) {
        return $this
            ->get('app.two_factor_authenticator')
            ->checkCode(
                $user,
                $code
            );
    }

    /**
     * Handle when a user tries to login via a recovery code.
     *
     * @param string                 $code
     * @param User                   $user
     * @param Request                $request
     * @param Session                $session
     * @param EntityManagerInterface $em
     *
     * @return bool Was it successfull?
     */
    private function handlePostRecoveryCode(
        $code,
        User $user,
        Request $request,
        Session $session,
        EntityManagerInterface $em
    ) {
        $userRecoveryCode = $this
            ->get('app.user_recovery_code_manager')
            ->get($code, $user);
        if ($userRecoveryCode) {
            $userRecoveryCode->setUsedAt(new \Datetime());

            $em->persist($userRecoveryCode);
            $em->flush();

            return true;
        }

        return false;
    }

    /**
     * @param Request                $request
     * @param Session                $session
     * @param EntityManagerInterface $em
     *
     * @throws BruteForceAttemptHttpException
     */
    private function handleTooManyAttempts(
        Request $request,
        Session $session,
        EntityManagerInterface $em
    ) {
        $ip = $request->getClientIp();
        $sessionId = $session->getId();
        $userAgent = $request->headers->get('User-Agent');
        $dateTimeFormat = $this->getParameter('date_time_format');

        $userBlockedAction = $em
            ->getRepository('CoreBundle:UserBlockedAction')
            ->getCurrentlyActive(
                $ip,
                $sessionId,
                $userAgent,
                'login.tfa'
            );
        if ($userBlockedAction) {
            throw new BruteForceAttemptException(
                /* @Meaning("Available arguments: %time%") */
                $this->get('translator')->trans(
                    'login.tfa.too_many_attempts.text',
                    [
                        '%time%' => $userBlockedAction->getExpiresAt()->format($dateTimeFormat),
                    ]
                )
            );
        }
    }

    /**
     * @param User    $user
     * @param Request $request
     * @param Session $session
     * @param string  $currentMethod
     * @param array   $alternativeMethods
     *
     * @return Response|null
     */
    private function handleMethodSwitch(
        User $user,
        Request $request,
        Session $session,
        $currentMethod,
        $alternativeMethods
    ) {
        $method = $request->query->get('method');

        if (in_array($method, array_keys($alternativeMethods))) {
            try {
                $this->container->get('app.brute_force_manager')
                    ->checkIfBlocked($request, 'login.tfa.method_switch');

                $session->set(
                    'two_factor_authentication_method',
                    $method
                );

                $this->get('app.two_factor_authentication_manager')
                    ->handleMethod($method, $user);

                $this->get('app.user_action_manager')
                    ->add(
                        'user.login.tfa.method_switch',
                        $this->get('translator')->trans(
                            'my.login.tfa.method_switch.user_action.text'
                        ),
                        [
                            'current_method' => $currentMethod,
                            'new_method' => $method,
                        ],
                        $user,
                        true,
                        'login.tfa.method_switch'
                    );
            } catch (BruteForceAttemptException $e) {
                $this->addFlash(
                    'danger',
                    $e->getMessage()
                );
            }

            return $this->redirectToRoute(
                'login.tfa'
            );
        } elseif (null !== $method) {
            $this->addFlash(
                'info',
                $this->get('translator')->trans(
                    'login.tfa.method_unavailable.text'
                )
            );
        }

        return null;
    }

    /**
     * Logs the failed login attempt by adding a user action,
     * and adding a user block if the user has actually
     * too many login attempts already.
     *
     * @param User   $user
     * @param string $method
     * @param string $code
     */
    private function handleFailedLoginAttempt(User $user, $method, $code)
    {
        $this->get('app.user_action_manager')
            ->add(
                'user.login.tfa.fail',
                $this->get('translator')->trans(
                    'login.tfa.fail.text'
                ),
                [
                    'method' => $method,
                    'code' => $code,
                ]
            );

        $this->get('app.brute_force_manager')
            ->handleUserBlockedAction(
                $user,
                'login.tfa',
                'user.login.tfa.fail'
            );
    }

    /**
     * @param User   $User
     * @param string $currentMethod
     *
     * @return array
     */
    private function getAlternativeMethods(User $user, $currentMethod)
    {
        $availableMethods = $user->getAvailableTFAMethods();

        // Ignore the current method
        if (isset($availableMethods[$currentMethod])) {
            unset($availableMethods[$currentMethod]);
        }

        return $availableMethods;
    }
}
