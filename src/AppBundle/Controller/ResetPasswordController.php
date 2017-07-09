<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Form;
use AppBundle\Entity\User;
use AppBundle\Form\Type\ResetPasswordType;
use AppBundle\Form\Type\ResetPasswordRequestType;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class ResetPasswordController extends Controller
{
    /**
     * @Route("/reset-password", name="reset_password")
     */
    public function resetPasswordAction(Request $request)
    {
        if ($this->isGranted('ROLE_USER')) {
            $this->addFlash(
                'info',
                $this->get('translator')->trans('general.already_logged_in')
            );

            return $this->redirectToRoute('home');
        }

        $code = $request->query->has('code')
            ? $request->query->get('code')
            : false
        ;
        $isPasswordResetRequest = empty($code);
        $alert = false;
        $alertMessage = '';

        if ($isPasswordResetRequest) {
            $form = $this->createForm(
                ResetPasswordRequestType::class,
                new User()
            );

            $this->handleResetPasswordRequest($request, $form, $alert, $alertMessage);
        } else {
            $form = $this->createForm(
                ResetPasswordType::class,
                new User()
            );

            $this->handleResetPassword($code, $request, $form, $alert, $alertMessage);
        }

        return $this->render(
            'AppBundle:Content:reset_password.html.twig',
            [
                'form' => $form->createView(),
                'alert' => $alert,
                'alert_message' => $alertMessage,
            ]
        );
    }

    /**
     * @param Request $request
     * @param Form    $form
     * @param bool    $alert
     * @param string  $alertMessage
     */
    private function handleResetPasswordRequest(Request $request, Form &$form, &$alert, &$alertMessage)
    {
        $em = $this->getDoctrine()->getManager();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $formUser = $form->getData();

            $user = $em
                ->getRepository('AppBundle:User')
                ->findOneByEmail($formUser->getEmail())
            ;

            if ($user) {
                $isPasswordCodeAlreadySent = $user->getResetPasswordCodeExpiresAt()
                    && new \DateTime() < $user->getResetPasswordCodeExpiresAt();
                if ($isPasswordCodeAlreadySent) {
                    $alert = 'info';
                    $alertMessage = $this->get('translator')->trans('reset_password.request.already_requested');
                } else {
                    // In the REALLY unlikely case that the reset password code wouldn't be unique
                    try {
                        $this->get('app.user_manager')->resetPasswordRequest($user);

                        $alert = 'success';
                        $alertMessage = $this->get('translator')->trans('reset_password.request.success');
                    } catch (\Exception $e) {
                        $alert = 'danger';
                        $alertMessage = $this->get('translator')->trans('general.something_went_wrong');
                    }
                }
            } else {
                $alert = 'danger';
                $alertMessage = $this->get('translator')->trans('reset_password.request.email_not_found');
            }
        }
    }

    /**
     * @param Request $request
     * @param Form    $form
     * @param bool    $alert
     * @param string  $alertMessage
     */
    private function handleResetPassword($code, Request $request, &$form, &$alert, &$alertMessage)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em
            ->getRepository('AppBundle:User')
            ->findOneByResetPasswordCode($code)
        ;

        if ($user) {
            $isResetPasswordCodeExpired = new \DateTime() > $user->getResetPasswordCodeExpiresAt();
            if ($isResetPasswordCodeExpired) {
                $alert = 'danger';
                $alertMessage = $this->get('translator')->trans('reset_password.code_expired');
            } else {
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $formUser = $form->getData();

                    $this->get('app.user_manager')->resetPassword($user, $formUser);

                    $alert = 'success';
                    $alertMessage = $this->get('translator')->trans('reset_password.success');
                }
            }
        } else {
            $alert = 'danger';
            $alertMessage = $this->get('translator')->trans('reset_password.code_not_found');
        }
    }
}
