<?php

namespace ApiBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class MyController extends Controller
{
    /********** Me **********/

    /**
     * @Route("/api/me", name="api.me")
     */
    public function meAction(Request $request)
    {
        return $this->json([
            'data' => $this->getUser()->toArray(),
        ]);
    }
}
