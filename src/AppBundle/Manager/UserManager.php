<?php

namespace AppBundle\Manager;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use AppBundle\Entity\User;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class UserManager
{
    use ContainerAwareTrait;

    /**
     * @param User   $user
     * @param bool   $persist Should the changes to the user entity be persisted to the database?
     *
     * @return bool
     */
    public function signupRequest(User $user, $persist = true)
    {
        $user
            ->setEmailActivationCode(
                md5(uniqid(null, true))
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
                'subject' => $this->container->get('translator')->trans(
                    'emails.user.signup.request.subject',
                    [
                        '%app_name%' => $this->container->getParameter('app_name'),
                    ]
                ),
                'to' => [$user->getEmail() => $user->getName()],
                'body' => 'AppBundle:Emails:User/signup_request.html.twig',
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
                'subject' => $this->container->get('translator')->trans(
                    'emails.user.signup.confirmation.subject',
                    [
                        '%app_name%' => $this->container->getParameter('app_name'),
                    ]
                ),
                'to' => [$user->getEmail() => $user->getName()],
                'body' => 'AppBundle:Emails:User/signup_confirmation.html.twig',
                'template_data' => [
                    'user' => $user,
                ],
            ])
        ;

        return true;
    }

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
                'subject' => $this->container->get('translator')->trans(
                    'emails.user.reset_password.confirmation.subject',
                    [
                        '%app_name%' => $this->container->getParameter('app_name'),
                    ]
                ),
                'to' => [$user->getEmail() => $user->getName()],
                'body' => 'AppBundle:Emails:User/reset_password_confirmation.html.twig',
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
        $user
            ->setResetPasswordCode(md5(uniqid(null, true)))
            ->setResetPasswordCodeExpiresAt(
                new \Datetime(
                    'now +'.$this->container->getParameter('reset_password_expiry_time')
            ))
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
                'subject' => $this->container->get('translator')->trans(
                    'emails.user.reset_password.request.subject',
                    [
                        '%app_name%' => $this->container->getParameter('app_name'),
                    ]
                ),
                'to' => [$user->getEmail() => $user->getName()],
                'body' => 'AppBundle:Emails:User/reset_password_request.html.twig',
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
    public function newEmailRequest(User $user, $persist = false)
    {
        $user
            ->setNewEmailCode(md5(uniqid(null, true)))
            ->setNewEmail($user->getEmail())
        ;

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
                ]
            );

        $this->container->get('app.mailer')
            ->swiftMessageInitializeAndSend([
                'subject' => $this->container->get('translator')->trans(
                    'emails.user.new_email.request.subject',
                    [
                        '%app_name%' => $this->container->getParameter('app_name'),
                    ]
                ),
                'to' => [$user->getNewEmail() => $user->getName()],
                'body' => 'AppBundle:Emails:User/new_email_request.html.twig',
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
        ;

        if ($persist) {
            $em = $this->container->get('doctrine.orm.entity_manager');
            $em->persist($user);
            $em->flush();
        }

        $this->container->get('app.mailer')
            ->swiftMessageInitializeAndSend([
                'subject' => $this->container->get('translator')->trans(
                    'emails.user.new_email_confirmation.subject',
                    [
                        '%app_name%' => $this->container->getParameter('app_name'),
                    ]
                ),
                'to' => [$user->getEmail() => $user->getName()],
                'body' => 'AppBundle:Emails:User/new_email_confirmation.html.twig',
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

    /**
     * @param User   $user
     * @param bool   $persist Should the changes to the user entity be persisted to the database?
     *
     * @return bool
     */
    public function emailActivationRequest(User $user, $persist = true)
    {
        $user
            ->setEmailActivationCode(
                md5(uniqid(null, true))
            )
        ;

        if ($persist) {
            $em = $this->container->get('doctrine.orm.entity_manager');
            $em->persist($user);
            $em->flush();
        }

        $this->container->get('app.mailer')
            ->swiftMessageInitializeAndSend([
                'subject' => $this->container->get('translator')->trans(
                    'emails.user.email_activation.request.subject',
                    [
                        '%app_name%' => $this->container->getParameter('app_name'),
                    ]
                ),
                'to' => [$user->getEmail() => $user->getName()],
                'body' => 'AppBundle:Emails:User/email_activation_request.html.twig',
                'template_data' => [
                    'user' => $user,
                ],
            ])
        ;

        $this->container->get('app.user_action_manager')
            ->add(
                'user.settings.email_activation.request',
                $this->container->get('translator')->trans(
                    'my.settings.email_activation.request.user_action.text'
                ),
                [],
                $user
            );

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
                'subject' => $this->container->get('translator')->trans(
                    'emails.user.email_activation.confirmation.subject',
                    [
                        '%app_name%' => $this->container->getParameter('app_name'),
                    ]
                ),
                'to' => [$user->getEmail() => $user->getName()],
                'body' => 'AppBundle:Emails:User/email_activation_confirmation.html.twig',
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
}
