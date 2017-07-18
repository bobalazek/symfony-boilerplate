<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormBuilder;
use AppBundle\Entity\User;
use AppBundle\Form\Type\SignupType;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class SignupController extends Controller
{
    /**
     * @Route("/signup", name="signup")
     */
    public function signupAction(Request $request)
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
        $emailActivationCode = $request->query->get('email_activation_code');

        $isConfirmation = !empty($id) && !empty($emailActivationCode);
        $alert = false;
        $alertMessage = '';

        $user = new User();
        $user->prepareUserRecoveryCodes(
            $this->getParameter('recovery_codes_count')
        );

        $form = $this->createForm(
            SignupType::class,
            $user
        );

        if ($isConfirmation) {
            $this->handleSignupConfirmation(
                $id,
                $emailActivationCode,
                $alert,
                $alertMessage
            );
        } else {
            $this->handleSignupRequest(
                $form,
                $request,
                $alert,
                $alertMessage
            );
        }

        return $this->render(
            'AppBundle:Content:signup.html.twig',
            [
                'form' => $form->createView(),
                'alert' => $alert,
                'alert_message' => $alertMessage,
            ]
        );
    }

    /**
     * @param string $id
     * @param string $emailActivationCode
     * @param string $alert
     * @param string $alertMessage
     */
    protected function handleSignupConfirmation(
        $id,
        $emailActivationCode,
        &$alert,
        &$alertMessage
    ) {
        $em = $this->getDoctrine()->getManager();
        $user = $em
            ->getRepository('AppBundle:User')
            ->findOneBy([
                'id' => $id,
                'emailActivationCode' => $emailActivationCode,
            ]);

        if ($user) {
            $this->get('app.user_manager')->signupConfirmation($user);

            $alert = 'success';
            $alertMessage = $this->get('translator')->trans(
                'signup.confirmation.success.text'
            );
        } else {
            $alert = 'danger';
            $alertMessage = $this->get('translator')->trans(
                'signup.confirmation.code_not_found.text'
            );
        }
    }

    /**
     * @param FormBuilder $form
     * @param Request     $request
     * @param string      $alert
     * @param string      $alertMessage
     */
    protected function handleSignupRequest(
        FormBuilder $form,
        Request $request,
        &$alert,
        &$alertMessage
    ) {
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $this->get('app.user_manager')->signupRequest($user);

            $alert = 'success';
            $alertMessage = $this->get('translator')->trans(
                'signup.request.success.text'
            );
        }
    }
}
