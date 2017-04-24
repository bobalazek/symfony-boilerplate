<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\Type\My\SettingsType;
use AppBundle\Form\Type\My\PasswordType;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class MyController extends Controller
{
    /**
     * @Route("/my/profile", name="my.profile")
     */
    public function profileAction(Request $request)
    {
        return $this->render(
            'AppBundle:Content:my/profile.html.twig'
        );
    }

    /**
     * @Route("/my/settings", name="my.settings")
     */
    public function settingsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(
            SettingsType::class,
            $this->getUser(),
            [
                'authorization_checker' => $this->get('security.authorization_checker'),
            ]
        );

        $userOld = clone $this->getUser();
        $userOldArray = $userOld->toArray();

        $newEmailCodeResponse = $this->handleNewEmailCode($request);
        if ($newEmailCodeResponse) {
            return $newEmailCodeResponse;
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            if ($userOld->getEmail() !== $user->getEmail()) {
                $user
                    ->setNewEmailCode(md5(uniqid(null, true)))
                    ->setNewEmail($user->getEmail())
                    ->setEmail($userOld->getEmail())
                ;

                $this->get('app.user_actions')->add(
                    'user.settings.email.change.request',
                    $this->get('translator')->trans('my.settings.new_email_request.user_action.text'),
                    [
                        'current' => $user->getEmail(),
                        'new' => $user->getNewEmail(),
                    ]
                );

                $this->get('app.mailer')
                    ->swiftMessageInitializeAndSend([
                        'subject' => $this->get('translator')->trans(
                            'my.settings.new_email_request.email.subject',
                            [
                                '%app_name%' => $this->getParameter('app_name'),
                            ]
                        ),
                        'to' => [$user->getNewEmail() => $user->getName()],
                        'body' => 'AppBundle:Emails:User/new_email.html.twig',
                        'template_data' => [
                            'user' => $user,
                        ],
                    ])
                ;

                $this->addFlash(
                    'success',
                    $this->get('translator')->trans('my.settings.new_email_request.flash_message')
                );
            }

            $em->persist($user);
            $em->flush();

            $this->get('app.user_actions')->add(
                'user.settings.change',
                $this->get('translator')->trans('my.settings.user_action.text'),
                [
                    'old' => $userOldArray,
                    'new' => $user->toArray(),
                ]
            );

            $this->addFlash(
                'success',
                $this->get('translator')->trans('my.settings.save.flash_message')
            );

            return $this->redirectToRoute('my.settings');
        } else {
            $em->refresh($this->getUser());
        }

        return $this->render(
            'AppBundle:Content:my/settings.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Check if the new email code is present (for confirming the new email address).
     */
    protected function handleNewEmailCode(Request $request)
    {
        $newEmailCode = $request->query->get('new_email_code');
        if ($newEmailCode) {
            $em = $this->getDoctrine()->getManager();
            $user = $this->getUser();

            $userByNewEmailCode = $em
                ->getRepository('AppBundle:User')
                ->findOneByNewEmailCode($newEmailCode);

            if (
                $userByNewEmailCode &&
                $userByNewEmailCode === $user
            ) {
                $oldEmail = $user->getEmail();

                $user
                    ->setNewEmailCode(null)
                    ->setEmail($user->getNewEmail())
                    ->setNewEmail(null)
                ;
                $em->persist($user);
                $em->flush();

                $this->get('app.mailer')
                    ->swiftMessageInitializeAndSend([
                        'subject' => $this->get('translator')->trans(
                            'my.settings.new_email.email.subject',
                            [
                                '%app_name%' => $this->getParameter('app_name'),
                            ]
                        ),
                        'to' => [$user->getEmail() => $user->getName()],
                        'body' => 'AppBundle:Emails:User/new_email_confirmation.html.twig',
                        'template_data' => [
                            'user' => $user,
                        ],
                    ])
                ;

                $this->get('app.user_actions')->add(
                    'user.settings.email.change',
                    $this->get('translator')->trans('my.settings.new_email.user_action.text'),
                    [
                        'old' => $oldEmail,
                        'new' => $user->getEmail(),
                    ]
                );

                $this->addFlash(
                    'success',
                    $this->get('translator')->trans('my.settings.new_email.flash_message')
                );
            } else {
                $this->addFlash(
                    'warning',
                    $this->get('translator')->trans('my.settings.new_email.code_invalid.flash_message')
               );
            }

            return $this->redirectToRoute('my.settings');
        }

        return;
    }

    /**
     * @Route("/my/password", name="my.password")
     */
    public function passwordAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(
            PasswordType::class,
            $this->getUser()
        );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            if ($user->getPlainPassword()) {
                $user->setPlainPassword(
                    $user->getPlainPassword(),
                    $this->container->get('security.password_encoder')
                );

                $em->persist($user);
                $em->flush();

                $this->get('app.user_actions')->add(
                    'user.settings.password.change',
                    $this->get('translator')->trans('my.password.user_action.text')
                );

                $this->addFlash(
                    'success',
                    $this->get('translator')->trans('my.password.flash_message')
                );
            }

            return $this->redirectToRoute('my.password');
        }

        return $this->render(
            'AppBundle:Content:my/password.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/my/actions", name="my.actions")
     */
    public function actionsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQueryBuilder()
            ->select('ua')
            ->from('AppBundle:UserAction', 'ua')
            ->where('ua.user = ?1')
            ->setParameter(1, $this->getUser())
        ;

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10,
            [
                'defaultSortFieldName' => 'ua.createdAt',
                'defaultSortDirection' => 'desc',
            ]
        );

        return $this->render(
            'AppBundle:Content:my/actions.html.twig',
            [
                'pagination' => $pagination,
            ]
        );
    }
}