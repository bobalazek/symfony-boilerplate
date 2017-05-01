<?php

namespace AdminBundle\Controller;

use JavierEguiluz\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;

class AdminController extends BaseAdminController
{
    public function prePersistEntity($entity) {
        $this->preUpdateEntity($entity);
    }
    
    public function preUpdateEntity($entity) {
        if (
            method_exists($entity, 'setPlainPassword') &&
            $entity->getPlainPassword()
        ) {
            $entity->setPlainPassword(
                $entity->getPlainPassword(),
                $this->container->get('security.password_encoder')
            );
        }
    }
}