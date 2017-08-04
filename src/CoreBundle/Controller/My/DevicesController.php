<?php

namespace CoreBundle\Controller\My;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class DevicesController extends Controller
{
    /**
     * @Route("/my/devices", name="my.devices")
     * @Security("has_role('ROLE_USER')")
     */
    public function actionsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQueryBuilder()
            ->select('ud')
            ->from('CoreBundle:UserDevice', 'ud')
            ->where('ud.user = ?1')
            ->andWhere('ud.deletedAt IS NULL')
            ->setParameter(1, $this->getUser())
        ;

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10,
            [
                'defaultSortFieldName' => 'ud.createdAt',
                'defaultSortDirection' => 'desc',
            ]
        );

        return $this->render(
            'CoreBundle:Content:my/devices.html.twig',
            [
                'pagination' => $pagination,
            ]
        );
    }
}
