<?php

namespace AdminBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class UserController extends Controller
{
    /**
     * @param int $id
     */
    public function impersonateAction($id)
    {
        $user = $this->admin->getSubject();
        if (null === $user) {
            throw $this->createNotFoundException(
                sprintf('Unable to find the object with id : %s', $id)
            );
        }

        return $this->redirectToRoute('home', [
            '_switch_user' => $user->getUsername(),
        ]);
    }

    /**
     * @param int $id
     */
    public function restoreAction($id)
    {
        $user = $this->admin->getSubject();
        if (null === $user) {
            throw $this->createNotFoundException(
                sprintf('Unable to find the object with id : %s', $id)
            );
        }

        $em = $this->getDoctrine()->getManager();

        $user->restore();
        $em->persist($user);
        $em->flush();

        $this->addFlash(
            'sonata_flash_success',
            sprintf('You have succesfully restored the user "%s".', $user)
        );

        return $this->redirect(
            $this->admin->generateObjectUrl('list', null)
        );
    }

    /***** Hooks *****/

    /**
     * @param Request                $request
     * @param CoreBundle\Entity\User $user
     */
    public function preDelete(Request $request, $user)
    {
        if ($user === $this->getUser()) {
            $this->addFlash(
                'sonata_flash_error',
                'You can not delete yourself.'
            );

            return $this->redirect(
                $this->admin->generateObjectUrl('list', null)
            );
        }
    }
}
