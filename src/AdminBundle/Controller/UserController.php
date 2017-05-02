<?php

namespace AdminBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController as Controller;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class UserController extends Controller
{
    /***** Actions *****/
    public function impersonateAction($id) {
        $user = $this->admin->getSubject();
        if (!$user) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }
        
        return $this->redirectToRoute('home', [
            '_switch_user' => $user->getUsername(),
        ]);
    }
}
