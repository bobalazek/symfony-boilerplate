<?php

namespace CoreBundle\Manager;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use libphonenumber\PhoneNumberFormat;
use CoreBundle\Entity\User;
use CoreBundle\Utils\Helpers;
use CoreBundle\Exception\BruteForceAttemptException;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class UserManager
{
    /** @var ContainerInterface */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /***** Signup *****/

    /**
     * @param User $user
     * @param bool $persist Should the changes to the user entity be persisted to the database?
     *
     * @return bool
     */
    public function signupRequest(User $user, $persist = true)
    {
        $user
            ->setEmailActivationCode(
                Helpers::getRandomString(32)
            )
            ->setPlainPassword(
                $user->getPlainPassword(),
                $this->container->get('security.password_encoder')
            )
        ;

        if ($persist) {
            $em = $this->container->get('doctrine.orm.entity_manager');
            $em->persist($user);
            $em->flush();
        }

        $this->container->get('app.mailer')
            ->swiftMessageInitializeAndSend([
                /* @Meaning("Available arguments: %app_name%") */
                'subject' => $this->container->get('translator')->trans(
                    'emails.user.signup.request.subject',
                    [
                        '%app_name%' => $this->container->getParameter('app_name'),
                    ]
                ),
                'to' => [$user->getEmail() => $user->getName()],
                'body' => 'CoreBundle:Emails:User/signup_request.html.twig',
                'template_data' => [
                    'user' => $user,
                ],
            ])
        ;

        return true;
    }

    /**
     * @param User $user
     * @param bool $persist Should the changes to the user entity be persisted to the database?
     *
     * @return bool
     */
    public function signupConfirmation(User $user, $persist = true)
    {
        $user
            ->setEmailActivationCode(null)
            ->setEmailActivatedAt(new \DateTime())
            ->enable()
        ;

        if ($persist) {
            $em = $this->container->get('doctrine.orm.entity_manager');
            $em->persist($user);
            $em->flush();
        }

        $this->container->get('app.mailer')
            ->swiftMessageInitializeAndSend([
                /* @Meaning("Available arguments: %app_name%") */
                'subject' => $this->container->get('translator')->trans(
                    'emails.user.signup.confirmation.subject',
                    [
                        '%app_name%' => $this->container->getParameter('app_name'),
                    ]
                ),
                'to' => [$user->getEmail() => $user->getName()],
                'body' => 'CoreBundle:Emails:User/signup_confirmation.html.twig',
                'template_data' => [
                    'user' => $user,
                ],
            ])
        ;

        return true;
    }

    /***** Reset Password *****/

    /**
     * @param User   $user
     * @param string $plainPassword
     * @param bool   $persist       Should the changes to the user entity be persisted to the database?
     *
     * @return bool
     */
    public function resetPasswordConfirmation(User $user, $plainPassword, $persist = true)
    {
        $user
            ->setResetPasswordCode(null)
            ->setResetPasswordCodeExpiresAt(null)
            ->setPlainPassword(
                $plainPassword,
                $this->container->get('security.password_encoder')
            )
        ;

        if ($persist) {
            $em = $this->container->get('doctrine.orm.entity_manager');
            $em->persist($user);
            $em->flush();
        }

        $this->container->get('app.user_action_manager')
            ->add(
                'user.reset_password.confirmation',
                $this->container->get('translator')->trans(
                    'reset_password.user_action.text'
                ),
                [],
                $user
            );

        $this->container->get('app.mailer')
            ->swiftMessageInitializeAndSend([
                /* @Meaning("Available arguments: %app_name%") */
                'subject' => $this->container->get('translator')->trans(
                    'emails.user.reset_password.confirmation.subject',
                    [
                        '%app_name%' => $this->container->getParameter('app_name'),
                    ]
                ),
                'to' => [$user->getEmail() => $user->getName()],
                'body' => 'CoreBundle:Emails:User/reset_password_confirmation.html.twig',
                'template_data' => [
                    'user' => $user,
                ],
            ])
        ;

        return true;
    }

    /**
     * @param User $user
     * @param bool $persist Should the changes to the user entity be persisted to the database?
     *
     * @return bool
     */
    public function resetPasswordRequest(User $user, $persist = true)
    {
        $expiresAt = (new \Datetime())->add(
            new \Dateinterval(
                'PT' . $this->container->getParameter('reset_password_expiry_time') . 'S'
            )
        );

        $user
            ->setResetPasswordCode(
                Helpers::getRandomString(32)
            )
            ->setResetPasswordCodeExpiresAt(
                $expiresAt
            )
        ;

        if ($persist) {
            $em = $this->container->get('doctrine.orm.entity_manager');
            $em->persist($user);
            $em->flush();
        }

        $this->container->get('app.user_action_manager')
            ->add(
                'user.reset_password.request',
                $this->container->get('translator')->trans(
                    'reset_password.request.user_action.text'
                ),
                [],
                $user
            );

        $this->container->get('app.mailer')
            ->swiftMessageInitializeAndSend([
                /* @Meaning("Available arguments: %app_name%") */
                'subject' => $this->container->get('translator')->trans(
                    'emails.user.reset_password.request.subject',
                    [
                        '%app_name%' => $this->container->getParameter('app_name'),
                    ]
                ),
                'to' => [$user->getEmail() => $user->getName()],
                'body' => 'CoreBundle:Emails:User/reset_password_request.html.twig',
                'template_data' => [
                    'user' => $user,
                ],
            ])
        ;

        return true;
    }

    /***** New Email *****/

    /**
     * @param Request $request
     * @param User    $user
     * @param User    $userOld
     * @param bool    $persist       Should the changes to the user entity be persisted to the database?
     * @param bool    $resetUserData should the data, that was manipulated in the form, be set back to original? Is useful, if the values weren't actually manipulated and the $user && $userOld are the same
     *
     * @return bool
     *
     * @throws BruteForceAttemptException
     */
    public function newEmailRequest(
        Request $request,
        User $user,
        User $userOld,
        $persist = false,
        $resetUserData = true
    ) {
        $this->container->get('app.brute_force_manager')
            ->checkIfBlocked($request, 'settings.new_email');

        $user
            ->setNewEmailCode(
                Helpers::getRandomString(32)
            )
        ;

        if ($resetUserData) {
            $user
                ->setNewEmail($user->getEmail())
                ->setEmail($userOld->getEmail())
            ;
        }

        if ($persist) {
            $em = $this->container->get('doctrine.orm.entity_manager');
            $em->persist($user);
            $em->flush();
        }

        $this->container->get('app.user_action_manager')
            ->add(
                'user.settings.new_email.request',
                $this->container->get('translator')->trans(
                    'my.settings.new_email.request.user_action.text'
                ),
                [
                    'current' => $user->getEmail(),
                    'new' => $user->getNewEmail(),
                ],
                $user,
                true,
                'settings.new_email'
            );

        $this->container->get('app.mailer')
            ->swiftMessageInitializeAndSend([
                /* @Meaning("Available arguments: %app_name%") */
                'subject' => $this->container->get('translator')->trans(
                    'emails.user.new_email.request.subject',
                    [
                        '%app_name%' => $this->container->getParameter('app_name'),
                    ]
                ),
                'to' => [$user->getNewEmail() => $user->getName()],
                'body' => 'CoreBundle:Emails:User/new_email_request.html.twig',
                'template_data' => [
                    'user' => $user,
                ],
            ])
        ;

        return true;
    }

    /**
     * @param User $user
     * @param bool $persist Should the changes to the user entity be persisted to the database?
     *
     * @return bool
     */
    public function newEmailConfirmation(User $user, $persist = true)
    {
        $oldEmail = $user->getEmail();
        $user
            ->setNewEmailCode(null)
            ->setEmail($user->getNewEmail())
            ->setNewEmail(null)
            ->setEmailActivatedAt(new \DateTime())
        ;

        if ($persist) {
            $em = $this->container->get('doctrine.orm.entity_manager');
            $em->persist($user);
            $em->flush();
        }

        $this->container->get('app.mailer')
            ->swiftMessageInitializeAndSend([
                /* @Meaning("Available arguments: %app_name%") */
                'subject' => $this->container->get('translator')->trans(
                    'emails.user.new_email_confirmation.subject',
                    [
                        '%app_name%' => $this->container->getParameter('app_name'),
                    ]
                ),
                'to' => [$user->getEmail() => $user->getName()],
                'body' => 'CoreBundle:Emails:User/new_email_confirmation.html.twig',
                'template_data' => [
                    'user' => $user,
                ],
            ])
        ;

        $this->container->get('app.user_action_manager')
            ->add(
                'user.settings.new_email.confirmation',
                $this->container->get('translator')->trans(
                    'my.settings.new_email.confirmation.user_action.text'
                ),
                [
                    'old' => $oldEmail,
                    'new' => $user->getEmail(),
                ]
            );

        return true;
    }

    /***** Email Activation *****/

    /**
     * @param Request $request
     * @param User    $user
     * @param bool    $persist Should the changes to the user entity be persisted to the database?
     *
     * @return bool
     *
     * @throws BruteForceAttemptException
     */
    public function emailActivationRequest(Request $request, User $user, $persist = true)
    {
        $this->container->get('app.brute_force_manager')
            ->checkIfBlocked($request, 'settings.email_activation');

        $user
            ->setEmailActivationCode(
                Helpers::getRandomString(32)
            )
        ;

        if ($persist) {
            $em = $this->container->get('doctrine.orm.entity_manager');
            $em->persist($user);
            $em->flush();
        }

        $this->container->get('app.user_action_manager')
            ->add(
                'user.settings.email_activation.request',
                $this->container->get('translator')->trans(
                    'my.settings.email_activation.request.user_action.text'
                ),
                [],
                $user,
                true,
                'settings.email_activation'
            );

        $this->container->get('app.mailer')
            ->swiftMessageInitializeAndSend([
                /* @Meaning("Available arguments: %app_name%") */
                'subject' => $this->container->get('translator')->trans(
                    'emails.user.email_activation.request.subject',
                    [
                        '%app_name%' => $this->container->getParameter('app_name'),
                    ]
                ),
                'to' => [$user->getEmail() => $user->getName()],
                'body' => 'CoreBundle:Emails:User/email_activation_request.html.twig',
                'template_data' => [
                    'user' => $user,
                ],
            ])
        ;

        return true;
    }

    /**
     * @param User $user
     * @param bool $persist Should the changes to the user entity be persisted to the database?
     *
     * @return bool
     */
    public function emailActivationConfirmation(User $user, $persist = true)
    {
        $user
            ->setEmailActivationCode(null)
            ->setEmailActivatedAt(new \DateTime())
        ;

        if ($persist) {
            $em = $this->container->get('doctrine.orm.entity_manager');
            $em->persist($user);
            $em->flush();
        }

        $this->container->get('app.mailer')
            ->swiftMessageInitializeAndSend([
                /* @Meaning("Available arguments: %app_name%") */
                'subject' => $this->container->get('translator')->trans(
                    'emails.user.email_activation.confirmation.subject',
                    [
                        '%app_name%' => $this->container->getParameter('app_name'),
                    ]
                ),
                'to' => [$user->getEmail() => $user->getName()],
                'body' => 'CoreBundle:Emails:User/email_activation_confirmation.html.twig',
                'template_data' => [
                    'user' => $user,
                ],
            ])
        ;

        $this->container->get('app.user_action_manager')
            ->add(
                'user.settings.email_activation.confirmation',
                $this->container->get('translator')->trans(
                    'my.settings.email_activation.confirmation.user_action.text'
                ),
                [],
                $user
            );

        return true;
    }

    /***** New Mobile *****/

    /**
     * @param Request $request
     * @param User    $user
     * @param User    $userOld
     * @param bool    $persist       Should the changes to the user entity be persisted to the database?
     * @param bool    $resetUserData should the data, that was manipulated in the form, be set back to original? Is useful, if the values weren't actually manipulated and the $user && $userOld are the same
     *
     * @return bool
     *
     * @throws BruteForceAttemptException
     */
    public function newMobileRequest(
        Request $request,
        User $user,
        User $userOld,
        $persist = false,
        $resetUserData = true
    ) {
        $this->container->get('app.brute_force_manager')
            ->checkIfBlocked($request, 'settings.new_mobile');

        $user
            ->setNewMobileCode(
                Helpers::getRandomString(8)
            )
        ;

        if ($resetUserData) {
            $user
                ->setNewMobile($user->getMobile())
                ->setMobile($userOld->getMobile())
            ;
        }

        if ($persist) {
            $em = $this->container->get('doctrine.orm.entity_manager');
            $em->persist($user);
            $em->flush();
        }

        $this->container->get('app.user_action_manager')
            ->add(
                'user.settings.new_mobile.request',
                $this->container->get('translator')->trans(
                    'my.settings.new_mobile.request.user_action.text'
                ),
                [
                    'current' => $user->getMobile(),
                    'new' => $user->getNewMobile(),
                ],
                $user,
                true,
                'settings.new_mobile'
            );

        $to = $this->container->get('libphonenumber.phone_number_util')
            ->format(
                $user->getNewMobile(),
                PhoneNumberFormat::INTERNATIONAL
            );

        $this->container->get('app.sms_sender')
            ->send(
                $to,
                /* @Meaning("Available arguments: %code%") */
                $this->container->get('translator')->trans(
                    'my.settings.new_mobile.request.sms.text',
                    [
                        '%code%' => $user->getNewMobileCode(),
                    ]
                )
            );

        return true;
    }

    /**
     * @param User $user
     * @param bool $persist Should the changes to the user entity be persisted to the database?
     *
     * @return bool
     */
    public function newMobileConfirmation(User $user, $persist = true)
    {
        $oldMobile = $user->getMobile();
        $user
            ->setNewMobileCode(null)
            ->setMobile($user->getNewMobile())
            ->setNewMobile(null)
            ->setMobileActivatedAt(new \DateTime())
        ;

        if ($persist) {
            $em = $this->container->get('doctrine.orm.entity_manager');
            $em->persist($user);
            $em->flush();
        }

        $this->container->get('app.mailer')
            ->swiftMessageInitializeAndSend([
                /* @Meaning("Available arguments: %app_name%") */
                'subject' => $this->container->get('translator')->trans(
                    'emails.user.new_mobile.confirmation.subject',
                    [
                        '%app_name%' => $this->container->getParameter('app_name'),
                    ]
                ),
                'to' => [$user->getEmail() => $user->getName()],
                'body' => 'CoreBundle:Emails:User/new_mobile_confirmation.html.twig',
                'template_data' => [
                    'user' => $user,
                ],
            ])
        ;

        $this->container->get('app.user_action_manager')
            ->add(
                'user.settings.new_mobile.confirmation',
                $this->container->get('translator')->trans(
                    'my.settings.new_mobile.confirmation.user_action.text'
                ),
                [
                    'old' => $oldMobile,
                    'new' => $user->getMobile(),
                ]
            );

        return true;
    }

    /***** Mobile Activation *****/

    /**
     * @param Request $request
     * @param User    $user
     * @param bool    $persist Should the changes to the user entity be persisted to the database?
     *
     * @return bool
     *
     * @throws BruteForceAttemptException
     */
    public function mobileActivationRequest(Request $request, User $user, $persist = true)
    {
        $this->container->get('app.brute_force_manager')
            ->checkIfBlocked($request, 'settings.mobile_activation');

        $user
            ->setMobileActivationCode(
                Helpers::getRandomString(8)
            )
        ;

        if ($persist) {
            $em = $this->container->get('doctrine.orm.entity_manager');
            $em->persist($user);
            $em->flush();
        }

        $this->container->get('app.user_action_manager')
            ->add(
                'user.settings.mobile_activation.request',
                $this->container->get('translator')->trans(
                    'my.settings.mobile_activation.request.user_action.text'
                ),
                [],
                $user,
                true,
                'settings.mobile_activation'
            );

        $to = $this->container->get('libphonenumber.phone_number_util')
            ->format(
                $user->getMobile(),
                PhoneNumberFormat::INTERNATIONAL
            );

        $this->container->get('app.sms_sender')
            ->send(
                $to,
                /* @Meaning("Available arguments: %code%") */
                $this->container->get('translator')->trans(
                    'my.settings.mobile_activation.request.sms.text',
                    [
                        '%code%' => $user->getMobileActivationCode(),
                    ]
                )
            );

        return true;
    }

    /**
     * @param User $user
     * @param bool $persist Should the changes to the user entity be persisted to the database?
     *
     * @return bool
     */
    public function mobileActivationConfirmation(User $user, $persist = true)
    {
        $user
            ->setMobileActivationCode(null)
            ->setMobileActivatedAt(new \DateTime())
        ;

        if ($persist) {
            $em = $this->container->get('doctrine.orm.entity_manager');
            $em->persist($user);
            $em->flush();
        }

        $this->container->get('app.mailer')
            ->swiftMessageInitializeAndSend([
                /* @Meaning("Available arguments: %app_name%") */
                'subject' => $this->container->get('translator')->trans(
                    'emails.user.mobile_activation.confirmation.subject',
                    [
                        '%app_name%' => $this->container->getParameter('app_name'),
                    ]
                ),
                'to' => [$user->getEmail() => $user->getName()],
                'body' => 'CoreBundle:Emails:User/mobile_activation_confirmation.html.twig',
                'template_data' => [
                    'user' => $user,
                ],
            ])
        ;

        $this->container->get('app.user_action_manager')
            ->add(
                'user.settings.mobile_activation.confirmation',
                $this->container->get('translator')->trans(
                    'my.settings.mobile_activation.confirmation.user_action.text'
                ),
                [],
                $user
            );

        return true;
    }
}
