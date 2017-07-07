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
class TwoFactorAuthenticationController extends Controller
{
    /**
     * @Route("/my/two-factor-authentication", name="my.two_factor_authentication")
     * @Security("has_role('ROLE_USER')")
     */
    public function twoFactorAuthenticationAction(Request $request)
    {
        return $this->render(
            'AppBundle:Content:my/two_factor_authentication/list.html.twig'
        );
    }

    /**
     * @Route("/my/two-factor-authentication/create", name="my.two_factor_authentication.create")
     * @Security("has_role('ROLE_USER')")
     */
    public function twoFactorAuthenticationCreateAction(Request $request)
    {
        return $this->render(
            'AppBundle:Content:my/two_factor_authentication/new.html.twig'
        );
    }

    /**
     * @Route("/my/two-factor-authentication/{id}/edit", name="my.two_factor_authentication.edit")
     * @Security("has_role('ROLE_USER')")
     */
    public function twoFactorAuthenticationEditAction($id, Request $request)
    {
        return $this->render(
            'AppBundle:Content:my/two_factor_authentication/edit.html.twig'
        );
    }
}
