<?php

namespace AdminBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class AdminController extends Controller
{
    /**
     * @Route("/admin", name="admin")
     */
    public function indexAction(Request $request)
    {
        $this->denyAccessUnlessGranted(
            'ROLE_ADMIN',
            null,
            'You have no permissions to access this page!'
        );

        return $this->render(
            'AdminBundle:Content:index.html.twig'
        );
    }
}
