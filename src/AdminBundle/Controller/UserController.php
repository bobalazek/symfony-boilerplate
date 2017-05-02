<?php

namespace AdminBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class UserController extends Controller
{
    public function impersonateAction($id)
    {
        $user = $this->admin->getSubject();
        if (!$user) {
            throw $this->createNotFoundException(
                sprintf('Unable to find the object with id : %s', $id)
            );
        }

        return $this->redirectToRoute('home', [
            '_switch_user' => $user->getUsername(),
        ]);
    }

    public function restoreAction($id)
    {
        $user = $this->admin->getSubject();
        if (!$user) {
            throw $this->createNotFoundException(
                sprintf('Unable to find the object with id : %s', $id)
            );
        }

        $em = $this->getDoctrine()->getManager();

        $user->restore();
        $em->persist($user);
        $em->flush();

        return $this->redirect(
            $this->admin->generateObjectUrl('list', null)
        );
    }

    /***** Hooks *****/
    // TODO: move inside UserAdmin::preRemove()?
    public function preDelete(Request $request, $user)
    {
        if ($user === $this->getUser()) {
            $this->addFlash('sonata_flash_error', 'You can not delete yourself.');

            return $this->redirect(
                $this->admin->generateObjectUrl('list', null)
            );
        }
    }
}
