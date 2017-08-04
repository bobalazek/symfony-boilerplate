<?php

namespace CoreBundle\Controller\My;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use CoreBundle\Form\Type\My\PasswordType;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class PasswordController extends Controller
{
    /**
     * @Route("/my/password", name="my.password")
     * @Security("has_role('ROLE_USER')")
     */
    public function passwordAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(
            PasswordType::class,
            $this->getUser()
        );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            if ($user->getPlainPassword()) {
                $user->setPlainPassword(
                    $user->getPlainPassword(),
                    $this->container->get('security.password_encoder')
                );

                $em->persist($user);
                $em->flush();

                $this->get('app.user_action_manager')->add(
                    'user.settings.password.change',
                    $this->get('translator')->trans(
                        'my.password.user_action.text'
                    )
                );

                $this->addFlash(
                    'success',
                    $this->get('translator')->trans(
                        'my.password.success.flash_message.text'
                    )
                );
            }

            return $this->redirectToRoute('my.password');
        }

        return $this->render(
            'CoreBundle:Content:my/password.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }
}
