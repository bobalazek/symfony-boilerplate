<?php

namespace ApiBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Noxlogic\RateLimitBundle\Annotation\RateLimit;
use CoreBundle\Entity\User;
use CoreBundle\Entity\Profile;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class SignupController extends Controller
{
    /**
     * @Route("/api/signup", name="api.signup")
     *
     * @RateLimit(limit=10, period=3600)
     */
    public function signupAction(Request $request)
    {
        $profile = new Profile();
        $profile
            ->setTitle($request->get('title'))
            ->setFirstName($request->get('first_name'))
            ->setLastName($request->get('last_name'))
        ;

        $user = new User();
        $user
            ->setUsername($request->get('username'))
            ->setEmail($request->get('email'))
            ->setPlainPassword(
                $request->get('password'),
                $this->get('security.password_encoder')
            )
            ->setProfile($profile)
        ;

        $errors = $this->get('validator')->validate(
            $user,
            null,
            ['signup']
        );
        if (count($errors) > 0) {
            return $this->json(
                $this->get('app.apifier')->errors($errors),
                400
            );
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        $this->get('app.user_manager')->signupRequest($user);

        return $this->json([
            'data' => [
                'message' => $this->get('translator')->trans(
                    'api.signup.request.success.text'
                ),
            ],
        ]);
    }
}
