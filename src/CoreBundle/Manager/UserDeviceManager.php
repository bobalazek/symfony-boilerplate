<?php

namespace CoreBundle\Manager;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Jenssegers\Agent\Agent;
use CoreBundle\Utils\Helpers;
use CoreBundle\Entity\User;
use CoreBundle\Entity\UserDevice;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class UserDeviceManager
{
    /** @var ContainerInterface */
    protected $container;

    /** @var UserDevice */
    protected $currentUserDevice = null;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param Request $request
     */
    public function getUid(Request $request)
    {
        return $request->query->has('_device_uid')
            ? $request->query->get('_device_uid')
            : ($request->cookies->has('device_uid')
                ? $request->cookies->get('device_uid')
                : ($request->headers->has('X-Device-UID')
                    ? $request->headers->get('X-Device-UID')
                    : null
                )
            );
    }

    /**
     * @param User    $user
     * @param Request $request
     */
    public function get(
        User $user,
        Request $request
    ) {
        if ($this->currentUserDevice !== null) {
            return $this->currentUserDevice;
        }

        $em = $this->container->get('doctrine.orm.entity_manager');
        $uid = $this->getUid($request);

        $userDevice = $em
            ->getRepository('CoreBundle:UserDevice')
            ->findOneBy([
                'user' => $user,
                'uid' => $uid,
            ]);

        if ($userDevice === null) {
            $userDevice = $this->create(
                $user,
                $request,
                $uid
            );
        }

        $this->currentUserDevice = $userDevice;

        return $userDevice;
    }

    /**
     * Creates a user device.
     *
     * @param User    $user
     * @param Request $request
     * @param sting   $uid
     *
     * @return UserDevice
     */
    public function create(
        User $user,
        Request $request,
        $uid
    ) {
        $session = $this->container->get('session');

        if (empty($uid)) {
            $uid = Helpers::getRandomString(64);
        }

        $userAgent = $request->headers->get('User-Agent');
        $agent = new Agent();
        $agent->setUserAgent($userAgent);

        $userDevice = new UserDevice();
        $userDevice
            ->setUid($uid)
            ->setName($agent->platform().' - '.$agent->browser())
            ->setIp($request->getClientIp())
            ->setUserAgent($userAgent)
            ->setSessionId($session->getId())
            ->setUser($user)
        ;

        /*
         * Set the attribute, so we can create the cookie
         *   at the end of the request (GeneralListener->onKernelResponse()).
         */
        $request->attributes->set(
            'device_uid',
            $userDevice->getUid()
        );

        return $userDevice;
    }

    /**
     * Is the current device trusted?
     *
     * @param User    $user
     * @param Request $request
     *
     * @return bool
     */
    public function isCurrentTrusted(
        User $user,
        Request $request
    ) {
        $userDevice = $this->get(
            $user,
            $request
        );

        return $userDevice->isTrusted();
    }

    /**
     * Set the current device as trusted.
     *
     * @param User    $user
     * @param Request $request
     *
     * @return bool
     */
    public function setCurrentAsTrusted(
        User $user,
        Request $request
    ) {
        $em = $this->container->get('doctrine.orm.entity_manager');

        $userDevice = $this->get(
            $user,
            $request
        );

        $userDevice->setTrusted(true);

        $em->persist($userDevice);
        $em->flush();

        return true;
    }
}
