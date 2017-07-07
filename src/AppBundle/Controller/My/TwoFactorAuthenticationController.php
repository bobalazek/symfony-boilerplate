<?php

namespace AppBundle\Controller\My;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormError;
use AppBundle\Form\Type\My\TwoFactorMethodType;
use AppBundle\Entity\UserTwoFactorMethod;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class TwoFactorAuthenticationController extends Controller
{
    /**
     * @Route("/my/two-factor-authentication", name="my.two_factor_authentication")
     * @Security("has_role('ROLE_USER')")
     */
    public function twoFactorAuthenticationAction(Request $request)
    {
        $userTwoFactorMethods = $this->getUser()->getUserTwoFactorMethods(true);

        return $this->render(
            'AppBundle:Content:my/two_factor_authentication/list.html.twig',
            [
                'user_two_factor_methods' => $userTwoFactorMethods,
            ]
        );
    }

    /**
     * @Route("/my/two-factor-authentication/create", name="my.two_factor_authentication.create")
     * @Security("has_role('ROLE_USER')")
     */
    public function twoFactorAuthenticationCreateAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $userTwoFactorMethods = $this->getUser()->getUserTwoFactorMethods(true);

        $hasEmailMethodImplemented = false;
        $hasBackupCodesMethodImplemented = false;
        foreach ($userTwoFactorMethods as $userTwoFactorMethod) {
            $method = $userTwoFactorMethod->getMethod();

            if ($method === 'email') {
                $hasEmailMethodImplemented = true;
            } elseif ($method === 'backup_codes') {
                $hasBackupCodesMethodImplemented = true;
            }
        }

        $form = $this->createForm(
            TwoFactorMethodType::class
        );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $userTwoFactorMethod = $form->getData();

            $userTwoFactorMethod
                ->setUser($this->getUser())
            ;

            $hasErrors = false;
            if (
                $hasEmailMethodImplemented &&
                $userTwoFactorMethod->getMethod() === 'email'
            ) {
                $error = new FormError(
                    'You have alread implemented the email method.'
                );
                $form->get('method')->addError($error);
                $hasErrors = true;
            } elseif (
                $hasBackupCodesMethodImplemented &&
                $userTwoFactorMethod->getMethod() === 'backup_codes'
            ) {
                $error = new FormError(
                    'You have alread implemented the backup codes method.'
                );
                $form->get('method')->addError($error);
                $hasErrors = true;
            }

            // TODO

            if (!$hasErrors) {
                $em->persist($userTwoFactorMethod);
                $em->flush();

                $this->addFlash(
                    'success',
                    $this->get('translator')->trans(
                        'my.two_factor_authentication.save.flash_message'
                    )
                );

                return $this->redirectToRoute('my.two_factor_authentication');
            }
        }

        return $this->render(
            'AppBundle:Content:my/two_factor_authentication/new.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/my/two-factor-authentication/{id}/edit", name="my.two_factor_authentication.edit")
     * @Security("has_role('ROLE_USER')")
     */
    public function twoFactorAuthenticationEditAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('AppBundle:UserTwoFactorMethod');

        $userTwoFactorMethod = $repository->findOneBy([
            'id' => $id,
            'user' => $this->getUser(),
        ]);

        if (
            $userTwoFactorMethod === null ||
            $userTwoFactorMethod->isDeleted()
        ) {
            throw $this->createNotFoundException(
                'This Two-factor method was not found'
            );
        }

        // TODO

        return $this->render(
            'AppBundle:Content:my/two_factor_authentication/edit.html.twig',
            [
                'user_two_factor_method' => $userTwoFactorMethod,
            ]
        );
    }

    /**
     * @Route("/my/two-factor-authentication/{id}/delete", name="my.two_factor_authentication.delete")
     * @Security("has_role('ROLE_USER')")
     */
    public function twoFactorAuthenticationDeleteAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('AppBundle:UserTwoFactorMethod');

        $userTwoFactorMethod = $repository->findOneBy([
            'id' => $id,
            'user' => $this->getUser(),
        ]);

        if (
            $userTwoFactorMethod === null ||
            $userTwoFactorMethod->isDeleted()
        ) {
            throw $this->createNotFoundException(
                'This Two-factor method was not found'
            );
        }

        if ($request->query->get('action') === 'confirm') {
            $em->remove($userTwoFactorMethod);
            $em->flush();

            $this->addFlash(
                'success',
                $this->get('translator')->trans('my.two_factor_authentication.delete.flash_message')
            );

            return $this->redirectToRoute('my.two_factor_authentication');
        }

        return $this->render(
            'AppBundle:Content:my/two_factor_authentication/delete.html.twig',
            [
                'user_two_factor_method' => $userTwoFactorMethod,
            ]
        );
    }
}
