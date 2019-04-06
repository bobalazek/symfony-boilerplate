<?php

namespace CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

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
        $locales = $this->getParameter('locales');

        return $this->render(
            'CoreBundle:Content:my/profile.html.twig',
            [
                'locales' => $locales,
            ]
        );
    }
}
