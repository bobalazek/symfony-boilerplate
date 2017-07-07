<?php

namespace AppBundle\Controller\My;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormError;
use AppBundle\Form\Type\My\TwoFactorMethodType;
use AppBundle\Entity\UserTwoFactorMethod;

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
        // TODO

        return $this->render(
            'AppBundle:Content:my/two_factor_authentication.html.twig'
        );
    }
}
