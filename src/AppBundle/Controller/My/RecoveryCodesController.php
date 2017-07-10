<?php

namespace AppBundle\Controller\My;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\Type\My\SettingsType;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class RecoveryCodesController extends Controller
{
    /**
     * @Route("/my/recovery-codes", name="my.recovery_codes")
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
            'AppBundle:Content:my/recovery_codes.html.twig',
            [
                'pagination' => $pagination,
            ]
        );
    }
}
