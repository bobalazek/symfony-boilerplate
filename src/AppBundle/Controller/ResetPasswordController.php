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
                $this->get('translator')->trans(
                    'general.already_logged_in'
                )
            );

            return $this->redirectToRoute('home');
        }

        $id = $request->query->get('id');
        $resetPasswordCode = $request->query->get('reset_password_code');
        $isRequest = empty($resetPasswordCode);
        $alert = false;
        $alertMessage = '';

        if ($isRequest) {
            $form = $this->createForm(
                ResetPasswordRequestType::class,
                new User()
            );

            $this->handleResetPasswordRequest(
                $request,
                $form,
                $alert,
                $alertMessage
            );
        } else {
            $form = $this->createForm(
                ResetPasswordType::class,
                new User()
            );

            $this->handleResetPasswordConfirmation(
                $id,
                $resetPasswordCode,
                $request,
                $form,
                $alert,
                $alertMessage
            );
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
     *
     * @return bool
     */
    private function handleResetPasswordRequest(
        Request $request,
        Form $form,
        &$alert,
        &$alertMessage
    ) {
        $em = $this->getDoctrine()->getManager();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $formUser = $form->getData();

            $user = $em
                ->getRepository('AppBundle:User')
                ->findOneByEmail($formUser->getEmail())
            ;

            if ($user === nul) {
                $alert = 'danger';
                $alertMessage = $this->get('translator')->trans(
                    'reset_password.request.email_not_found.text'
                );

                return false;
            }

            $isPasswordCodeAlreadySent = $user->getResetPasswordCodeExpiresAt()
                && new \DateTime() < $user->getResetPasswordCodeExpiresAt();
            if ($isPasswordCodeAlreadySent) {
                $alert = 'info';
                $alertMessage = $this->get('translator')->trans(
                    'reset_password.request.already_requested.text'
                );

                return false;
            }

            $this->get('app.user_manager')->resetPasswordRequest($user);

            $alert = 'success';
            $alertMessage = $this->get('translator')->trans(
                'reset_password.request.success.text'
            );

            return true;
        }
    }

    /**
     * @param int     $request
     * @param string  $resetPasswordCode
     * @param Request $request
     * @param Form    $form
     * @param bool    $alert
     * @param string  $alertMessage
     *
     * @return bool
     */
    private function handleResetPasswordConfirmation(
        $id,
        $resetPasswordCode,
        Request $request,
        Form $form,
        &$alert,
        &$alertMessage
    ) {
        $em = $this->getDoctrine()->getManager();
        $user = $em
            ->getRepository('AppBundle:User')
            ->findOneBy([
                'id' => $id,
                'resetPasswordCode' => $resetPasswordCode,
            ]);

        if ($user === null) {
            $alert = 'danger';
            $alertMessage = $this->get('translator')->trans(
                'reset_password.confirmation.code_not_found.text'
            );

            return false;
        }
        $isResetPasswordCodeExpired = new \DateTime() > $user->getResetPasswordCodeExpiresAt();
        if ($isResetPasswordCodeExpired) {
            $alert = 'danger';
            $alertMessage = $this->get('translator')->trans(
                'reset_password.confirmation.code_expired.text'
            );

            return false;
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $formUser = $form->getData();

            $this->get('app.user_manager')
                ->resetPasswordConfirmation(
                    $user,
                    $formUser->getPlainPassword()
                );

            $alert = 'success';
            $alertMessage = $this->get('translator')->trans(
                'reset_password.confirmation.success.text'
            );
        }

        return true;
    }
}
