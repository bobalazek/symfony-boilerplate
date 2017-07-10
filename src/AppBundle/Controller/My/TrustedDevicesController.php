<?php

namespace AppBundle\Controller\My;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class TrustedDevicesController extends Controller
{
    /**
     * @Route("/my/trusted-devices", name="my.trusted_devices")
     * @Security("has_role('ROLE_USER')")
     */
    public function trustedDevicesAction(Request $request)
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
            'AppBundle:Content:my/trusted_devices.html.twig',
            [
                'pagination' => $pagination,
            ]
        );
    }
}
