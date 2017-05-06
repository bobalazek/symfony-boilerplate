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

        $code = $request->query->has('code')
            ? $request->query->get('code')
            : false;
        $isSignupConfirmation = !empty($code);
        $alert = false;
        $alertMessage = '';

        $form = $this->createForm(
            SignupType::class,
            new User()
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
            $user
                ->setActivationCode(null)
                ->setActivatedAt(new \DateTime())
                ->enable()
            ;

            $em->persist($user);
            $em->flush();

            $this->get('app.mailer')
                ->swiftMessageInitializeAndSend([
                    'subject' => $this->get('translator')->trans(
                        'signup.confirmation.email.subject',
                        [
                            '%app_name%' => $this->getParameter('app_name'),
                        ]
                    ),
                    'to' => [$user->getEmail() => $user->getName()],
                    'body' => 'AppBundle:Emails:User/signup_confirmation.html.twig',
                    'template_data' => [
                        'user' => $user,
                    ],
                ])
            ;

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

            $user->setPlainPassword(
                $user->getPlainPassword(),
                $this->get('security.password_encoder')
            );

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->get('app.mailer')
                ->swiftMessageInitializeAndSend([
                    'subject' => $this->get('translator')->trans(
                        'signup.email.subject',
                        [
                            '%app_name%' => $this->getParameter('app_name'),
                        ]
                    ),
                    'to' => [$user->getEmail() => $user->getName()],
                    'body' => 'AppBundle:Emails:User/signup.html.twig',
                    'template_data' => [
                        'user' => $user,
                    ],
                ])
            ;

            $alert = 'success';
            $alertMessage = $this->get('translator')->trans('signup.success');
        }
    }
}
