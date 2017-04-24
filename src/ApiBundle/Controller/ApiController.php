<?php

namespace ApiBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class ApiController extends Controller
{
    /********** Me **********/

    /**
     * @Route("/api", name="api")
     */
    public function indexAction(Request $request)
    {
        return $this->json([
            'message' => 'Hello API!',
        ]);
    }
}