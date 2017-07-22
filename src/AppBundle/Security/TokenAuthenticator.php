<?php

namespace AppBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class TokenAuthenticator extends AbstractGuardAuthenticator
{
    /**
     * @param Request $request
     *
     * @return array
     */
    public function getCredentials(Request $request)
    {
        // Skip the authentication, if we're on those pages
        if (in_array($request->getPathInfo(), [
            '/api/login',
            '/api/signup',
            '/api/reset-password',
        ])) {
            return;
        }

        $id = $request->headers->has('X-AUTH-USER-ID')
            ? $request->headers->get('X-AUTH-USER-ID')
            : ($request->request->has('_user_id')
                ? $request->request->get('_user_id')
                : $request->query->get('_user_id')
            );
        $token = $request->headers->has('X-AUTH-USER-TOKEN')
            ? $request->headers->get('X-AUTH-USER-TOKEN')
            : ($request->request->has('_user_token')
                ? $request->request->get('_user_token')
                : $request->query->get('_user_token')
            );

        return [
            'id' => $id,
            'token' => $token,
        ];
    }

    /**
     * @param array                 $credentials
     * @param UserProviderInterface $userProvider
     *
     * @return array
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        return $userProvider->loadUserByIdAndToken(
            $credentials['id'],
            $credentials['token']
        );
    }

    /**
     * @param array         $credentials
     * @param UserInterface $user
     *
     * @return bool
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    /**
     * @param Request        $request
     * @param TokenInterface $token
     * @param string         $providerKey
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return null;
    }

    /**
     * @param Request                 $request
     * @param AuthenticationException $exception
     *
     * @return JsonResponse
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new JsonResponse([
            'error' => [
                'message' => strtr(
                    $exception->getMessageKey(),
                    $exception->getMessageData()
                ),
            ],
        ], Response::HTTP_FORBIDDEN);
    }

    /**
     * @param Request                 $request
     * @param AuthenticationException $exception
     *
     * @return JsonResponse
     */
    public function start(Request $request, AuthenticationException $exception = null)
    {
        return new JsonResponse([
            'error' => [
                'message' => 'Authentication Required',
            ],
        ], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @return bool
     */
    public function supportsRememberMe()
    {
        return false;
    }
}
