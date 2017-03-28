<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class AppController extends Controller
{
    /**
     * @Route("/", name="home")
     */
    public function indexAction(Request $request)
    {
        return $this->render(
            'AppBundle:Content:index.html.twig'
        );
    }

    /**
     * @Route("/help", name="help")
     */
    public function helpAction(Request $request)
    {
        return $this->render(
            'AppBundle:Content:pages/help.html.twig'
        );
    }

    /**
     * @Route("/terms", name="terms")
     */
    public function termsAction(Request $request)
    {
        return $this->render(
            'AppBundle:Content:pages/terms.html.twig'
        );
    }

    /**
     * @Route("/disclaimer", name="disclaimer")
     */
    public function disclaimerAction(Request $request)
    {
        return $this->render(
            'AppBundle:Content:pages/disclaimer.html.twig'
        );
    }

    /**
     * @Route("/imprint", name="imprint")
     */
    public function imprintAction(Request $request)
    {
        return $this->render(
            'AppBundle:Content:pages/imprint.html.twig'
        );
    }

    /**
     * @Route("/privacy", name="privacy")
     */
    public function privacyAction(Request $request)
    {
        return $this->render(
            'AppBundle:Content:pages/privacy.html.twig'
        );
    }

    /**
     * @Route("/contact", name="contact")
     */
    public function contactAction(Request $request)
    {
        return $this->render(
            'AppBundle:Content:pages/contact.html.twig'
        );
    }
}
