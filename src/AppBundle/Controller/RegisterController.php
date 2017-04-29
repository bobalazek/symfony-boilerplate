<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\User;
use AppBundle\Form\Type\RegisterType;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class RegisterController extends Controller
{
    /**
     * @Route("/register", name="register")
     */
    public function registerAction(Request $request)
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
        $isRegisterConfirmation = !empty($code);
        $alert = false;
        $alertMessage = '';

        $form = $this->createForm(
            RegisterType::class,
            new User()
        );

        if ($isRegisterConfirmation) {
            $this->handleRegisterConfirmation($code, $alert, $alertMessage);
        } else {
            $this->handleRegister($form, $request, $alert, $alertMessage);
        }

        return $this->render(
            'AppBundle:Content:register.html.twig',
            [
                'form' => $form->createView(),
                'alert' => $alert,
                'alert_message' => $alertMessage,
            ]
        );
    }

    private function handleRegisterConfirmation($code, &$alert, &$alertMessage)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em
            ->getRepository('AppBundle:User')
            ->findOneByActivationCode($code)
        ;

        if ($user) {
            $user
                ->setActivationCode(null)
                ->enable()
            ;

            $em->persist($user);
            $em->flush();

            $this->get('app.mailer')
                ->swiftMessageInitializeAndSend([
                    'subject' => $this->get('translator')->trans(
                        'register_confirmation.email.subject',
                        [
                            '%app_name%' => $this->getParameter('app_name'),
                        ]
                    ),
                    'to' => [$user->getEmail() => $user->getName()],
                    'body' => 'AppBundle:Emails:User/register_confirmation.html.twig',
                    'template_data' => [
                        'user' => $user,
                    ],
                ])
            ;

            $alert = 'success';
            $alertMessage = $this->get('translator')->trans('register.confirmation.success');
        } else {
            $alert = 'danger';
            $alertMessage = $this->get('translator')->trans('register.confirmation.code_not_found');
        }
    }

    private function handleRegister(&$form, Request $request, &$alert, &$alertMessage)
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
                        'register.email.subject',
                        [
                            '%app_name%' => $this->getParameter('app_name'),
                        ]
                    ),
                    'to' => [$user->getEmail() => $user->getName()],
                    'body' => 'AppBundle:Emails:User/register.html.twig',
                    'template_data' => [
                        'user' => $user,
                    ],
                ])
            ;

            $alert = 'success';
            $alertMessage = $this->get('translator')->trans('register.success');
        }
    }
}
