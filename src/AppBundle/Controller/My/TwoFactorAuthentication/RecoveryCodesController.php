<?php

namespace AppBundle\Controller\My\TwoFactorAuthentication;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class RecoveryCodesController extends Controller
{
    /**
     * @Route("/my/tfa/recovery-codes", name="my.tfa.recovery_codes")
     * @Security("has_role('ROLE_USER')")
     */
    public function recoveryCodesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQueryBuilder()
            ->select('urc')
            ->from('AppBundle:UserRecoveryCode', 'urc')
            ->where('urc.user = ?1')
            ->setParameter(1, $this->getUser())
        ;

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10,
            [
                'defaultSortFieldName' => 'urc.createdAt',
                'defaultSortDirection' => 'desc',
            ]
        );

        return $this->render(
            'AppBundle:Content:my/tfa/recovery_codes.html.twig',
            [
                'pagination' => $pagination,
            ]
        );
    }
}
