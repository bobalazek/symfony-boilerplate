<?php

namespace AppBundle\Controller\My;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\Type\My\TwoFactorAuthenticationType;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class TwoFactorAuthenticationController extends Controller
{
    /**
     * @Route("/my/tfa", name="my.tfa")
     * @Security("has_role('ROLE_USER')")
     */
    public function tfaAction(Request $request) {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(
            TwoFactorAuthenticationType::class,
            $this->getUser()
        );

        $userOld = clone $this->getUser();
        $userOldArray = $userOld->toArray();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

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
                    'my.tfa.save.success.flash_message.text'
                )
            );

            return $this->redirectToRoute('my.tfa');
        }

        return $this->render(
            'AppBundle:Content:my/tfa.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }
}
