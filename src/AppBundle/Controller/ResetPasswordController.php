<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
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

    private function handleResetPasswordRequest(Request $request, &$form, &$alert, &$alertMessage)
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
                $isPasswordCodeAlreadySent = $user->getTimeResetPasswordCodeExpires()
                    && new \DateTime() < $user->getTimeResetPasswordCodeExpires();
                if ($isPasswordCodeAlreadySent) {
                    $alert = 'info';
                    $alertMessage = $this->get('translator')->trans('reset_password.request.already_requested');
                } else {
                    $user
                        ->setResetPasswordCode(md5(uniqid(null, true)))
                        ->setTimeResetPasswordCodeExpires(
                            new \Datetime(
                                'now +'.$this->getParameter('reset_password_expiry_time')
                        ));

                    $em->persist($user);

                    // In the REALLY unlikely case that the reset password code wouldn't be unique
                    try {
                        $em->flush();

                        $this->get('app.user_actions')->add(
                            'user.password_reset.request',
                            $this->get('translator')->trans('reset_password.request.user_action.text'),
                            [],
                            $user
                        );

                        $this->get('app.mailer')
                            ->swiftMessageInitializeAndSend([
                                'subject' => $this->get('translator')->trans(
                                    'reset_password.request.email.subject',
                                    [
                                    '%app_name%' => $this->getParameter('app_name'),
                                    ]
                                ),
                                'to' => [$user->getEmail() => $user->getName()],
                                'body' => 'AppBundle:Emails:User/reset_password.html.twig',
                                'template_data' => [
                                    'user' => $user,
                                ],
                            ])
                        ;

                        $alert = 'success';
                        $alertMessage = $this->get('translator')->trans('reset_password.request.success');
                    } catch (\Exception $e) {
                        $alert = 'danger';
                        $alertMessage = $this->get('translator')->trans('something_went_wrong');
                    }
                }
            } else {
                $alert = 'danger';
                $alertMessage = $this->get('translator')->trans('reset_password.request.email_not_found');
            }
        }
    }

    private function handleResetPassword($code, Request $request, &$form, &$alert, &$alertMessage)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em
            ->getRepository('AppBundle:User')
            ->findOneByResetPasswordCode($code)
        ;

        if ($user) {
            $isResetPasswordCodeExpired = new \DateTime() > $user->getTimeResetPasswordCodeExpires();
            if ($isResetPasswordCodeExpired) {
                $alert = 'danger';
                $alertMessage = $this->get('translator')->trans('reset_password.code_expired');
            } else {
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $formUser = $form->getData();

                    $user
                        ->setResetPasswordCode(null)
                        ->setTimeResetPasswordCodeExpires(null)
                        ->setPlainPassword(
                            $formUser->getPlainPassword(),
                            $this->get('security.password_encoder')
                        )
                    ;

                    $em->persist($user);
                    $em->flush();

                    $this->get('app.user_actions')->add(
                        'user.password_reset',
                        $this->get('translator')->trans('reset_password.user_action.text'),
                        [],
                        $user
                    );

                    $this->get('app.mailer')
                        ->swiftMessageInitializeAndSend([
                            'subject' => $this->get('translator')->trans(
                                'reset_password.email.subject',
                                [
                                '%app_name%' => $this->getParameter('app_name'),
                                ]
                            ),
                            'to' => [$user->getEmail() => $user->getName()],
                            'body' => 'AppBundle:Emails:User/reset_password_confirmation.html.twig',
                            'template_data' => [
                                'user' => $user,
                            ],
                        ])
                    ;

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
