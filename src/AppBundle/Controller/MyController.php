<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\Type\My\PasswordType;

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
        return $this->render(
            'AppBundle:Content:my/profile.html.twig'
        );
    }
}
