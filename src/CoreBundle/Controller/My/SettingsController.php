<?php

namespace CoreBundle\Controller\My;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use CoreBundle\Exception\BruteForceAttemptException;
use CoreBundle\Form\Type\My\SettingsType;
use CoreBundle\Entity\User;

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
        $user = $this->getUser();

        $form = $this->createForm(
            SettingsType::class,
            $user
        );

        $userOld = clone $user;
        $userOldArray = $userOld->toArray();

        $queryDataResponse = $this->handleQueryData(
            $request,
            $user
        );
        if ($queryDataResponse) {
            return $queryDataResponse;
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            if ($userOld->getEmail() !== $user->getEmail()) {
                try {
                    $this->get('app.user_manager')
                        ->newEmailRequest($request, $user, $userOld);

                    $this->addFlash(
                        'success',
                        $this->get('translator')->trans(
                            'my.settings.new_email.request.success.flash_message.text'
                        )
                    );
                } catch (BruteForceAttemptException $e) {
                    $this->addFlash(
                        'warning',
                        $e->getMessage()
                    );
                }
            }

            if (
                !empty($user->getMobile()) &&
                (string) $userOld->getMobile() !== (string) $user->getMobile()
            ) {
                try {
                    $this->get('app.user_manager')
                        ->newMobileRequest($request, $user, $userOld);

                    $this->addFlash(
                        'success',
                        $this->get('translator')->trans(
                            'my.settings.new_mobile.request.success.flash_message.text'
                        )
                    );
                } catch (BruteForceAttemptException $e) {
                    $this->addFlash(
                        'warning',
                        $e->getMessage()
                    );
                }
            } elseif (
                empty($user->getMobile()) &&
                (string) $userOld->getMobile() !== (string) $user->getMobile()
            ) {
                $user->setMobileActivatedAt(null);
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
            $em->refresh($user);
        }

        return $this->render(
            'CoreBundle:Content:my/settings.html.twig',
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
        $user = $this->getUser();

        /***** Actions *****/
        $action = $request->query->get('action');
        if ($action === 'resend_email_activation') {
            try {
                $this->get('app.user_manager')
                    ->emailActivationRequest(
                        $request,
                        $user
                    );

                $this->addFlash(
                    'success',
                    $this->get('translator')->trans(
                        'my.settings.email_activation.request.success.flash_message.text'
                    )
               );
            } catch (BruteForceAttemptException $e) {
                $this->addFlash(
                   'warning',
                   $e->getMessage()
               );
            }

            return $this->redirectToRoute('my.settings');
        } elseif ($action === 'resend_mobile_activation') {
            try {
                $this->get('app.user_manager')
                    ->mobileActivationRequest(
                        $request,
                        $user
                    );

                $this->addFlash(
                    'success',
                    $this->get('translator')->trans(
                        'my.settings.mobile_activation.request.code_resent.flash_message.text'
                    )
               );
            } catch (BruteForceAttemptException $e) {
                $this->addFlash(
                   'warning',
                   $e->getMessage()
               );
            }

            return $this->redirectToRoute('my.settings');
        } elseif ($action === 'resend_new_email') {
            try {
                $this->get('app.user_manager')
                    ->newEmailRequest(
                        $request,
                        $user,
                        $user,
                        true,
                        false
                    );

                $this->addFlash(
                    'success',
                    $this->get('translator')->trans(
                        'my.settings.new_email.request.code_resent.flash_message.text'
                    )
                );
            } catch (BruteForceAttemptException $e) {
                $this->addFlash(
                    'warning',
                    $e->getMessage()
                );
            }

            return $this->redirectToRoute('my.settings');
        } elseif ($action === 'resend_new_mobile') {
            try {
                $this->get('app.user_manager')
                    ->newMobileRequest(
                        $request,
                        $user,
                        $user,
                        true,
                        false
                    );

                $this->addFlash(
                    'success',
                    $this->get('translator')->trans(
                        'my.settings.new_mobile.request.code_resent.flash_message.text'
                    )
                );
            } catch (BruteForceAttemptException $e) {
                $this->addFlash(
                    'warning',
                    $e->getMessage()
                );
            }

            return $this->redirectToRoute('my.settings');
        } elseif ($action === 'cancel_new_email') {
            $user
                ->setNewEmail(null)
                ->setNewEmailCode(null)
            ;
            $em->persist($user);
            $em->flush();

            $this->addFlash(
                'success',
                $this->get('translator')->trans(
                    'my.settings.new_email.request.cancel.flash_message.text'
                )
            );

            return $this->redirectToRoute('my.settings');
        } elseif ($action === 'cancel_new_mobile') {
            $user
                ->setNewMobile(null)
                ->setNewMobileCode(null)
            ;
            $em->persist($user);
            $em->flush();

            $this->addFlash(
                'success',
                $this->get('translator')->trans(
                    'my.settings.new_mobile.request.cancel.flash_message.text'
                )
            );

            return $this->redirectToRoute('my.settings');
        }

        /***** Email activation code *****/
        $emailActivationCode = $request->query->get('email_activation_code');
        if ($emailActivationCode !== null) {
            $userByEmailActivationCode = $em
                ->getRepository('CoreBundle:User')
                ->findOneBy([
                    'id' => $user->getId(),
                    'emailActivationCode' => $emailActivationCode,
                ]);

            if ($userByEmailActivationCode) {
                $this->get('app.user_manager')
                    ->emailActivationConfirmation($user);

                $this->addFlash(
                    'success',
                    $this->get('translator')->trans(
                        'my.settings.email_activation.confirmation.success.flash_message.text'
                    )
                );
            } else {
                $this->addFlash(
                    'warning',
                    $this->get('translator')->trans(
                        'my.settings.email_activation.confirmation.code_invalid.flash_message.text'
                    )
               );
            }

            return $this->redirectToRoute('my.settings');
        }

        /***** Mobile activation code *****/
        $mobileActivationCode = $request->query->get('mobile_activation_code');
        if ($mobileActivationCode !== null) {
            $userByMobileActivationCode = $em
                ->getRepository('CoreBundle:User')
                ->findOneBy([
                    'id' => $user->getId(),
                    'mobileActivationCode' => $mobileActivationCode,
                ]);

            if ($userByMobileActivationCode) {
                $this->get('app.user_manager')->mobileActivationConfirmation($user);

                $this->addFlash(
                    'success',
                    $this->get('translator')->trans(
                        'my.settings.mobile_activation.confirmation.success.flash_message.text'
                    )
                );
            } else {
                $this->addFlash(
                    'warning',
                    $this->get('translator')->trans(
                        'my.settings.mobile_activation.confirmation.code_invalid.flash_message.text'
                    )
               );
            }

            return $this->redirectToRoute('my.settings');
        }

        /***** New email code *****/
        $newEmailCode = $request->query->get('new_email_code');
        if ($newEmailCode !== null) {
            $userByNewEmailCode = $em
                ->getRepository('CoreBundle:User')
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
                        'my.settings.new_email.confirmation.success.flash_message.text'
                    )
                );
            } else {
                $this->addFlash(
                    'warning',
                    $this->get('translator')->trans(
                        'my.settings.new_email.confirmation.code_invalid.flash_message.text'
                    )
               );
            }

            return $this->redirectToRoute('my.settings');
        }

        /***** New mobile code *****/
        $newMobileCode = $request->query->get('new_mobile_code');
        if ($newMobileCode !== null) {
            $userByNewMobileCode = $em
                ->getRepository('CoreBundle:User')
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
                        'my.settings.new_mobile.confirmation.success.flash_message.text'
                    )
                );
            } else {
                $this->addFlash(
                    'warning',
                    $this->get('translator')->trans(
                        'my.settings.new_mobile.confirmation.code_invalid.flash_message.text'
                    )
               );
            }

            return $this->redirectToRoute('my.settings');
        }

        return null;
    }
}
