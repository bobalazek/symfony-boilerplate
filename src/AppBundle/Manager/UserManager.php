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
     * @param User $user
     */
    public function signup(User $user)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        $user->setPlainPassword(
            $user->getPlainPassword(),
            $this->container->get('security.password_encoder')
        );

        $em->persist($user);
        $em->flush();

        $this->container->get('app.mailer')
            ->swiftMessageInitializeAndSend([
                'subject' => $this->container->get('translator')->trans(
                    'signup.email.subject',
                    [
                        '%app_name%' => $this->container->getParameter('app_name'),
                    ]
                ),
                'to' => [$user->getEmail() => $user->getName()],
                'body' => 'AppBundle:Emails:User/signup.html.twig',
                'template_data' => [
                    'user' => $user,
                ],
            ])
        ;

        return true;
    }

    /**
     * @param User $user
     */
    public function signupConfirmation(User $user)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        $user
            ->setEmailActivationCode(null)
            ->setEmailActivatedAt(new \DateTime())
            ->enable()
            ->verifyEmail()
        ;

        $em->persist($user);
        $em->flush();

        $this->container->get('app.mailer')
            ->swiftMessageInitializeAndSend([
                'subject' => $this->container->get('translator')->trans(
                    'signup.confirmation.email.subject',
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
     * @param User $user
     * @param User $formUser
     */
    public function resetPassword(User $user, User $formUser)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        $user
            ->setResetPasswordCode(null)
            ->setResetPasswordCodeExpiresAt(null)
            ->setPlainPassword(
                $formUser->getPlainPassword(),
                $this->container->get('security.password_encoder')
            )
        ;

        $em->persist($user);
        $em->flush();

        $this->get('app.user_action_manager')->add(
            'user.password_reset',
            $this->container->get('translator')->trans('reset_password.user_action.text'),
            [],
            $user
        );

        $this->container->get('app.mailer')
            ->swiftMessageInitializeAndSend([
                'subject' => $this->container->get('translator')->trans(
                    'reset_password.email.subject',
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
     */
    public function resetPasswordRequest(User $user)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        $user
            ->setResetPasswordCode(md5(uniqid(null, true)))
            ->setResetPasswordCodeExpiresAt(
                new \Datetime(
                    'now +'.$this->container->getParameter('reset_password_expiry_time')
            ))
        ;

        $em->persist($user);
        $em->flush();

        $this->container->get('app.user_action_manager')->add(
            'user.password_reset.request',
            $this->container->get('translator')->trans('reset_password.request.user_action.text'),
            [],
            $user
        );

        $this->container->get('app.mailer')
            ->swiftMessageInitializeAndSend([
                'subject' => $this->container->get('translator')->trans(
                    'reset_password.request.email.subject',
                    [
                        '%app_name%' => $this->container->getParameter('app_name'),
                    ]
                ),
                'to' => [$user->getEmail() => $user->getName()],
                'body' => 'AppBundle:Emails:User/reset_password.html.twig',
                'template_data' => [
                    'user' => $user,
                ],
            ])
        ;

        return true;
    }
}
