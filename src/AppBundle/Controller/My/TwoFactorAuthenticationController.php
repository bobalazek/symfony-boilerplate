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
     * @Route("/my/two-factor-authentication", name="my.two_factor_authentication")
     * @Security("has_role('ROLE_USER')")
     */
    public function twoFactorAuthenticationAction(Request $request)
    {
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

            $this->get('app.user_actions')->add(
                'user.settings.change',
                $this->get('translator')->trans('my.settings.user_action.text'),
                [
                    'old' => $userOldArray,
                    'new' => $user->toArray(),
                ]
            );

            $this->addFlash(
                'success',
                $this->get('translator')->trans(
                    'my.two_factor_authentication.save.flash_message'
                )
            );

            return $this->redirectToRoute('my.two_factor_authentication');
        }

        return $this->render(
            'AppBundle:Content:my/two_factor_authentication.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/my/two-factor-authentication/email", name="my.two_factor_authentication.email")
     * @Security("has_role('ROLE_USER')")
     */
    public function twoFactorAuthenticationEmailAction(Request $request)
    {
        // TODO
    }

    /**
     * @Route("/my/two-factor-authentication/backup-codes", name="my.two_factor_authentication.backup_codes")
     * @Security("has_role('ROLE_USER')")
     */
    public function twoFactorAuthenticationBackupCodesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQueryBuilder()
            ->select('ubc')
            ->from('AppBundle:UserBackupCode', 'ubc')
            ->where('ubc.user = ?1')
            ->setParameter(1, $this->getUser())
        ;

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10,
            [
                'defaultSortFieldName' => 'ubc.createdAt',
                'defaultSortDirection' => 'desc',
            ]
        );

        return $this->render(
            'AppBundle:Content:my/two_factor_authentication/backup_codes.html.twig',
            [
                'pagination' => $pagination,
            ]
        );
    }

    /**
     * @Route("/my/two-factor-authentication/trusted-devices", name="my.two_factor_authentication.trusted_devices")
     * @Security("has_role('ROLE_USER')")
     */
    public function twoFactorAuthenticationTrustedDevicesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQueryBuilder()
            ->select('utd')
            ->from('AppBundle:UserTrustedDevice', 'utd')
            ->where('utd.user = ?1')
            ->setParameter(1, $this->getUser())
        ;

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10,
            [
                'defaultSortFieldName' => 'utd.createdAt',
                'defaultSortDirection' => 'desc',
            ]
        );

        return $this->render(
            'AppBundle:Content:my/two_factor_authentication/trusted_devices.html.twig',
            [
                'pagination' => $pagination,
            ]
        );
    }
}
