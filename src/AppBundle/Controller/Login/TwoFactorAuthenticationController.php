<?php

namespace AppBundle\Controller\Login;

use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Exception\BruteForceAttemptException;
use AppBundle\Entity\User;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class TwoFactorAuthenticationController extends Controller
{
    /**
     * @Route("/login/two-factor-authentication", name="login.two_factor_authentication")
     */
    public function loginTwoFactorAuthenticationAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session = $this->get('session');

        if (!$session->get('two_factor_authentication_in_progress')) {
            $this->addFlash(
                'info',
                $this->get('translator')->trans('general.already_logged_in')
            );

            return $this->redirectToRoute('home');
        }

        $user = $this->getUser();
        $method = $session->get('two_factor_authentication_method');
        $alternativeMethods = $this->getAlternativeMethods(
            $user,
            $method
        );

        // Check if the user has switched the 2FA method
        $methodSwitchResponse = $this->handleMethodSwitch(
            $request,
            $session,
            $method,
            $alternativeMethods
        );
        if ($methodSwitchResponse) {
            return $methodSwitchResponse;
        }

        if ($request->getMethod() === 'POST') {
            $code = $request->request->get('code');

            $postResponse = $this->handlePost(
                $method,
                $code,
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
            'AppBundle:Content:login/two_factor_authentication.html.twig',
            [
                'method' => $method,
                'alternative_methods' => $alternativeMethods,
                'code' => $request->query->get('code'),
            ]
        );
    }

    /***** Handle post methods *****/

    /**
     * @param string        $method
     * @param string        $code
     * @param User          $user
     * @param Request       $request
     * @param Session       $session
     * @param EntityManager $em
     *
     * @return Response|null
     */
    private function handlePost(
        $method,
        $code,
        $user,
        Request $request,
        Session $session,
        EntityManager $em
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
                'login.two_factor_authentication'
            );
        }

        $success = false;
        if (
            $method === 'email' ||
            $method === 'sms'
        ) {
            $success = $this->handlePostLoginCode(
                $method,
                $code,
                $user,
                $request,
                $session,
                $em
            );
        } elseif ($method === 'authenticator') {
            $success = $this->handlePostAuthenticator(
                $code,
                $request,
                $session,
                $em
            );
        } elseif ($method === 'recovery_code') {
            $success = $this->handlePostRecoveryCode(
                $code,
                $request,
                $session,
                $em
            );
        }

        if (!$success) {
            $this->addFlash(
                'danger',
                $this->get('translator')->trans(
                    'login.two_factor_authentication.code_invalid'
                )
            );

            $this->handleFailedLoginAttempt($user);

            return null;
        }

        $response = $this->redirectToRoute('home');

        $this->addFlash(
            'success',
            $this->get('translator')->trans(
                'login.two_factor_authentication.success'
            )
        );

        $session->remove(
            'two_factor_authentication_in_progress'
        );

        $this->get('app.user_action_manager')->add(
            'user.login.2fa',
            'User has been logged in via Two-factor authentication!'
        );

        return $response;
    }

    /**
     * Handle the post request, if it is the email method.
     * We use the same function for email & sms 2FA methods.
     *
     * @param string        $method
     * @param string        $code
     * @param User          $user
     * @param Request       $request
     * @param Session       $session
     * @param EntityManager $em
     *
     * @return bool Was it successfull?
     */
    private function handlePostLoginCode(
        $method,
        $code,
        User $user,
        Request $request,
        Session $session,
        EntityManager $em
    ) {
        $userLoginCodeExists = $this->get('app.user_login_code_manager')
            ->exists($code, $user);

        if ($userLoginCodeExists) {
            return true;
        }

        return false;
    }

    /**
     * Handle when a user tries to login with an authenticator.
     *
     * @param string        $code
     * @param User          $user
     * @param Request       $request
     * @param Session       $session
     * @param EntityManager $em
     *
     * @return bool Was it successfull?
     */
    private function handlePostAuthenticator(
        $code,
        User $user,
        Request $request,
        Session $session,
        EntityManager $em
    ) {
        // TODO

        return false;
    }

    /**
     * Handle when a user tries to login via a recovery code.
     *
     * @param string        $code
     * @param User          $user
     * @param Request       $request
     * @param Session       $session
     * @param EntityManager $em
     *
     * @return bool Was it successfull?
     */
    private function handlePostRecoveryCode(
        $code,
        User $user,
        Request $request,
        Session $session,
        EntityManager $em
    ) {
        // TODO

        return false;
    }

    /**
     * @param Request       $request
     * @param Session       $session
     * @param EntityManager $em
     *
     * @throws BruteForceAttemptHttpException
     */
    private function handleTooManyAttempts(
        Request $request,
        Session $session,
        EntityManager $em
    ) {
        $ip = $request->getClientIp();
        $sessionId = $session->getId();
        $userAgent = $request->headers->get('User-Agent');
        $dateTimeFormat = $this->getParameter('date_time_format');

        $userLoginBlock = $em->getRepository('AppBundle:UserLoginBlock')
            ->getCurrentlyActive(
                $ip,
                $sessionId,
                $userAgent,
                'login.2fa'
            );
        if ($userLoginBlock) {
            throw new BruteForceAttemptException(
                $this->get('translator')->trans(
                    'Your account has been blocked from logging it. The block will be released at %time%',
                    [
                        '%time%' => $userLoginBlock->getExpiresAt()->format($dateTimeFormat),
                    ]
                )
            );
        }
    }

    /**
     * @param Request $request
     * @param Session $session
     * @param string  $currentMethod
     * @param array   $alternativeMethods
     *
     * @return Response|null
     */
    private function handleMethodSwitch(
        Request $request,
        Session $session,
        $currentMethod,
        $alternativeMethods
    ) {
        $method = $request->query->get('method');

        if (in_array($method, array_keys($alternativeMethods))) {
            $session->set(
                'two_factor_authentication_method',
                $method
            );

            $this->get('app.user_action_manager')->add(
                'user.login.2fa.method_switch',
                $this->get('translator')->trans(
                    'my.login.2fa.method_switch.text'
                ),
                [
                    'current_method' => $currentMethod,
                    'new_method' => $method,
                ]
            );

            return $response = $this->redirectToRoute(
                'login.two_factor_authentication'
            );
        } elseif ($method !== null) {
            $this->addFlash(
                'info',
                $this->get('translator')->trans(
                    'login.two_factor_authentication.method_unavailable'
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
     * @param User $user
     */
    private function handleFailedLoginAttempt(User $user)
    {
        $this->get('app.user_action_manager')->add(
            'user.login.2fa.fail',
            $this->get('translator')->trans(
                'my.login.2fa.fail.text'
            ),
            [
                'code' => $code,
            ]
        );

        $this->get('app.brute_force_manager')
            ->handleUserLoginBlocks(
                $user,
                'login.2fa',
                'user.login.2fa.fail'
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
