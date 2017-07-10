<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
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
                $this->get('translator')->trans('general.already_logged_in')
            );

            return $this->redirectToRoute('home');
        }

        $recoveryCodesParameters = $this->getParameter('recovery_codes');

        $code = $request->query->has('code')
            ? $request->query->get('code')
            : false;
        $isSignupConfirmation = !empty($code);
        $alert = false;
        $alertMessage = '';

        $user = new User();
        $user->prepareUserRecoveryCodes(
            $recoveryCodesParameters['count']
        );

        $form = $this->createForm(
            SignupType::class,
            $user
        );

        if ($isSignupConfirmation) {
            $this->handleSignupConfirmation($code, $alert, $alertMessage);
        } else {
            $this->handleSignup($form, $request, $alert, $alertMessage);
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

    private function handleSignupConfirmation($code, &$alert, &$alertMessage)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em
            ->getRepository('AppBundle:User')
            ->findOneByActivationCode($code)
        ;

        if ($user) {
            $this->get('app.user_manager')->signupConfirmation($user);

            $alert = 'success';
            $alertMessage = $this->get('translator')->trans('signup.confirmation.success');
        } else {
            $alert = 'danger';
            $alertMessage = $this->get('translator')->trans('signup.confirmation.code_not_found');
        }
    }

    private function handleSignup(&$form, Request $request, &$alert, &$alertMessage)
    {
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $this->get('app.user_manager')->signup($user);

            $alert = 'success';
            $alertMessage = $this->get('translator')->trans('signup.success');
        }
    }
}
