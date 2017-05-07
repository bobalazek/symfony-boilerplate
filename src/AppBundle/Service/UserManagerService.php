<?php

namespace AppBundle\Service;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use AppBundle\Entity\User;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class UserManagerService
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
            ->setActivationCode(null)
            ->setActivatedAt(new \DateTime())
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
}
