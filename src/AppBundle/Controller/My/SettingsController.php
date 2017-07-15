<?php

namespace AppBundle\Controller\My;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\Type\My\SettingsType;
use AppBundle\Entity\User;

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

        $queryDataResponse = $this->handleQueryData(
            $request,
            $this->getUser()
        );
        if ($queryDataResponse) {
            return $queryDataResponse;
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            if ($userOld->getEmail() !== $user->getEmail()) {
                $this->get('app.user_manager')
                    ->newEmailRequest($user, $userOld);

                $this->addFlash(
                    'success',
                    $this->get('translator')->trans(
                        'my.settings.new_email.success.flash_message.text'
                    )
                );
            }

            if (
                !empty($user->getMobile()) &&
                $userOld->getMobile() !== $user->getMobile()
            ) {
                $this->get('app.user_manager')
                    ->newMobileRequest($user, $userOld);

                $this->addFlash(
                    'success',
                    $this->get('translator')->trans(
                        'my.settings.new_mobile.success.flash_message.text'
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
     * Also check for actions.
     *
     * @param Request $request
     * @param User    $user
     *
     * @return Response|null
     */
    protected function handleQueryData(Request $request, User $user)
    {
        $em = $this->getDoctrine()->getManager();

        /***** Actions *****/
        $action = $request->query->get('action');
        if ($action === 'resend_activation_email') {
            $this->get('app.user_manager')->emailActivationRequest(
                $this->getUser()
            );

            $this->addFlash(
                'success',
                $this->get('translator')->trans(
                    'my.settings.email_activation.code_resent.flash_message.text'
                )
           );

            return $this->redirectToRoute('my.settings');
        } elseif ($action === 'resend_activation_mobile') {
            $this->get('app.user_manager')->mobileActivationRequest(
                $this->getUser()
            );

            $this->addFlash(
                'success',
                $this->get('translator')->trans(
                    'my.settings.mobile_activation.code_resent.flash_message.text'
                )
           );

            return $this->redirectToRoute('my.settings');
        }

        /***** Email activation code *****/
        $emailActivationCode = $request->query->get('email_activation_code');
        if ($emailActivationCode !== null) {
            $userByEmailActivationCode = $em
                ->getRepository('AppBundle:User')
                ->findOneBy([
                    'id' => $user->getId(),
                    'emailActivationCode' => $emailActivationCode,
                ]);

            if ($userByEmailActivationCode) {
                $this->get('app.user_manager')->emailActivationConfirmation($user);

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

        /***** Mobile activation code *****/
        $mobileActivationCode = $request->query->get('mobile_activation_code');
        if ($mobileActivationCode !== null) {
            $userByMobileActivationCode = $em
                ->getRepository('AppBundle:User')
                ->findOneBy([
                    'id' => $user->getId(),
                    'mobileActivationCode' => $mobileActivationCode,
                ]);

            if ($userByMobileActivationCode) {
                $this->get('app.user_manager')->mobileActivationConfirmation($user);

                $this->addFlash(
                    'success',
                    $this->get('translator')->trans(
                        'my.settings.mobile_activation.success.flash_message.text'
                    )
                );
            } else {
                $this->addFlash(
                    'warning',
                    $this->get('translator')->trans(
                        'my.settings.mobile_activation.success.code_invalid.flash_message.text'
                    )
               );
            }

            return $this->redirectToRoute('my.settings');
        }

        /***** New email code *****/
        $newEmailCode = $request->query->get('new_email_code');
        if ($newEmailCode !== null) {
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

        /***** New mobile code *****/
        $newMobileCode = $request->query->get('new_mobile_code');
        if ($newMobileCode !== null) {
            $userByNewMobileCode = $em
                ->getRepository('AppBundle:User')
                ->findOneBy([
                    'id' => $user->getId(),
                    'newMobileCode' => $newMobileCode,
                ]);

            if ($userByNewMobileCode) {
                $this->get('app.user_manager')
                    ->newMobileConfirmation($user);

                $this->addFlash(
                    'success',
                    $this->get('translator')->trans(
                        'my.settings.new_mobile.success.flash_message.text'
                    )
                );
            } else {
                $this->addFlash(
                    'warning',
                    $this->get('translator')->trans(
                        'my.settings.new_mobile.code_invalid.flash_message.text'
                    )
               );
            }

            return $this->redirectToRoute('my.settings');
        }

        return null;
    }
}
