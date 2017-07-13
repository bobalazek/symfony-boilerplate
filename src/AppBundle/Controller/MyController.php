<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\Type\My\PasswordType;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class MyController extends Controller
{
    /**
     * @Route("/my/profile", name="my.profile")
     * @Security("has_role('ROLE_USER')")
     */
    public function profileAction(Request $request)
    {
        return $this->render(
            'AppBundle:Content:my/profile.html.twig'
        );
    }

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
                    $this->get('translator')->trans('my.password.user_action.text')
                );

                $this->addFlash(
                    'success',
                    $this->get('translator')->trans('my.password.success.flash_message.text')
                );
            }

            return $this->redirectToRoute('my.password');
        }

        return $this->render(
            'AppBundle:Content:my/password.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/my/actions", name="my.actions")
     * @Security("has_role('ROLE_USER')")
     */
    public function actionsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQueryBuilder()
            ->select('ua')
            ->from('AppBundle:UserAction', 'ua')
            ->where('ua.user = ?1')
            ->setParameter(1, $this->getUser())
        ;

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10,
            [
                'defaultSortFieldName' => 'ua.createdAt',
                'defaultSortDirection' => 'desc',
            ]
        );

        return $this->render(
            'AppBundle:Content:my/actions.html.twig',
            [
                'pagination' => $pagination,
            ]
        );
    }
}
