<?php

namespace AppBundle\Controller\My;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\Type\My\SettingsType;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class SettingsController extends Controller
{
    /**
     * @Route("/my/settings", name="my.settings")
     * @Security("has_role('ROLE_USER')")
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

        $newEmailCodeResponse = $this->handleEmailCodes(
            $request,
            $this->getUser()
        );
        if ($newEmailCodeResponse) {
            return $newEmailCodeResponse;
        }

        $actionsResponse = $this->handleActions($request);
        if ($actionsResponse) {
            return $actionsResponse;
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            if ($userOld->getEmail() !== $user->getEmail()) {
                // Because the form has already changed that value, we need to set it back to original
                $user->setEmail(
                    $userOld->getEmail()
                );

                $this->get('app.user_manager')
                    ->newEmailRequest($user);

                $this->addFlash(
                    'success',
                    $this->get('translator')->trans(
                        'my.settings.new_email.success.flash_message.text'
                    )
                );
            }

            $em->persist($user);
            $em->flush();

            $this->get('app.user_action_manager')->add(
                'user.settings.change',
                $this->get('translator')->trans(
                    'my.settings.user_action.text'
                ),
                [
                    'old' => $userOldArray,
                    'new' => $user->toArray(),
                ]
            );

            $this->addFlash(
                'success',
                $this->get('translator')->trans(
                    'my.settings.save.success.flash_message.text'
                )
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
     * Check if the new email code is present (for confirming the new email address) or
     * or email code, for email activation/validation.
     *
     * @param Request $request
     * @param User    $user
     *
     * @return Response|null
     */
    protected function handleEmailCodes(Request $request, User $user)
    {
        $em = $this->getDoctrine()->getManager();

        $emailActivationCode = $request->query->get('email_activation_code');
        $newEmailCode = $request->query->get('new_email_code');

        // Email code
        if ($emailActivationCode) {
            $userByEmailActivationCode = $em
                ->getRepository('AppBundle:User')
                ->findOneBy([
                    'id' => $user->getId(),
                    'emailActivationCode' => $emailActivationCode,
                ]);

            if ($userByEmailActivationCode) {
                $this->get('app.user_manager')->signupConfirmation($user);

                $this->addFlash(
                    'success',
                    $this->get('translator')->trans(
                        'my.settings.email_activation.success.flash_message.text'
                    )
                );
            } else {
                $this->addFlash(
                    'warning',
                    $this->get('translator')->trans(
                        'my.settings.email_activation.success.code_invalid.flash_message.text'
                    )
               );
            }

            return $this->redirectToRoute('my.settings');
        }

        // New email code
        if ($newEmailCode) {
            $userByNewEmailCode = $em
                ->getRepository('AppBundle:User')
                ->findOneBy([
                    'id' => $user->getId(),
                    'newEmailCode' => $newEmailCode,
                ]);

            if ($userByNewEmailCode) {
                $this->get('app.user_manager')
                    ->newEmailConfirmation($user);

                $this->addFlash(
                    'success',
                    $this->get('translator')->trans(
                        'my.settings.new_email.success.flash_message.text'
                    )
                );
            } else {
                $this->addFlash(
                    'warning',
                    $this->get('translator')->trans(
                        'my.settings.new_email.code_invalid.flash_message.text'
                    )
               );
            }

            return $this->redirectToRoute('my.settings');
        }

        return null;
    }

    /**
     * @param Request $request
     *
     * @return Response|null
     */
    protected function handleActions(Request $request)
    {
        $action = $request->query->get('action');
        if ($action) {
            if ($action === 'resend_activation_email') {
                // TODO

                return $this->redirectToRoute('my.settings');
            } elseif ($action === 'resend_activation_mobile') {
                // TODO

                return $this->redirectToRoute('my.settings');
            }
        }

        return null;
    }
}
