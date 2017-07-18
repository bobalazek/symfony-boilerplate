<?php

namespace AppBundle\Controller\My;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class ActionsController extends Controller
{
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
            ->andWhere('ua.deletedAt IS NULL')
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
