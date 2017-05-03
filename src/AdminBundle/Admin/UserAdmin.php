<?php

namespace AdminBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class UserAdmin extends AbstractAdmin
{
    use ContainerAwareTrait;

    protected $roleChoices = [
        'Super admin' => 'ROLE_SUPER_ADMIN',
        'Admin' => 'ROLE_ADMIN',
        'User' => 'ROLE_USER',
    ];

    protected function configureFormFields(FormMapper $formMapper)
    {
        $user = $this->getSubject();

        $formMapper
            ->with('Profile', ['class' => 'col-md-4'])
                ->add('profile.title', 'text', [
                    'label' => 'Title',
                ])
                ->add('profile.firstName', 'text', [
                    'label' => 'First name',
                ])
                ->add('profile.lastName', 'text', [
                    'label' => 'Last name',
                ])
            ->end()
            ->with('Account', ['class' => 'col-md-4'])
                ->add('username', 'text')
                ->add('email', 'email')
                ->add('plainPassword', 'repeated', [
                    'required' => $user->getId()
                        ? false
                        : true,
                    'type' => 'password',
                    'first_options' => [
                        'label' => 'Password',
                    ],
                    'second_options' => [
                        'label' => 'Repeat password',
                    ],
                ])
            ->end()
            ->with('Roles', ['class' => 'col-md-4'])
                ->add('roles', 'choice', [
                    'label' => false,
                    'multiple' => true,
                    'expanded' => true,
                    'choices' => $this->roleChoices,
                ])
            ->end()
            ->with('Statuses', ['class' => 'col-md-4'])
                ->add('enabled')
                ->add('verified')
                ->add('warned')
                ->add('locked')
                ->add('lockedReason')
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('username')
            ->add('email')
            ->add('profile.firstName', null, [
                'label' => 'First name',
            ])
            ->add('profile.lastName', null, [
                'label' => 'Last name',
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('username')
            ->addIdentifier('email')
            ->add('profile.firstName', null, [
                'label' => 'First name',
            ])
            ->add('profile.lastName', null, [
                'label' => 'Last name',
            ])
            ->add('roles', 'choice', [ // HACK, to show it as a string
                'multiple' => true,
                'choices' => $this->roleChoices,
            ])
            ->add('enabled')
            ->add('verified')
            ->add('warned')
            ->add('locked')
            ->add('lastActiveAt')
            ->add('createdAt')
            ->add('deletedAt')
            ->add('_action', 'actions', [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                    'impersonate' => [
                        'template' => 'AdminBundle:User:list__action_impersonate.html.twig',
                    ],
                    'restore' => [
                        'template' => 'AdminBundle:User:list__action_restore.html.twig',
                    ],
                ],
            ])
        ;
    }

    public function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->with('Profile', ['class' => 'col-md-4'])
                ->add('profile.title', null, [
                    'label' => 'Title',
                ])
                ->add('profile.firstName', null, [
                    'label' => 'First name',
                ])
                ->add('profile.lastName', null, [
                    'label' => 'Last name',
                ])
            ->end()
            ->with('Account', ['class' => 'col-md-4'])
                ->add('username')
                ->add('email')
                ->add('newEmail')
                ->add('lastActiveAt')
                ->add('updatedAt')
                ->add('createdAt')
                ->add('deletedAt')
            ->end()
            ->with('Roles', ['class' => 'col-md-4'])
                ->add('roles', 'choice', [
                    'multiple' => true,
                    'expanded' => true,
                    'choices' => $this->roleChoices,
                ])
            ->end()
            ->with('Statuses', ['class' => 'col-md-4'])
                ->add('enabled')
                ->add('verified')
                ->add('warned')
                ->add('locked')
                ->add('lockedReason')
                ->add('newsletter')
            ->end()
            ->with('Actions')
                ->add('userActions', 'html_template', [
                    'html' => "{% include 'AdminBundle:User:list__user_actions_table.html.twig' %}",
                ])
            ->end()
        ;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('impersonate', $this->getRouterIdParameter().'/impersonate');
        $collection->add('restore', $this->getRouterIdParameter().'/restore');
    }

    /***** Hooks *****/
    public function prePersist($user)
    {
        $this->preparePlainPassword($user);
    }

    public function preUpdate($user)
    {
        $this->preparePlainPassword($user);
        $this->preventLockingYourself($user);
        $this->preventDisablingYourself($user);
        // TODO: prevent degrading users with bigger premissions
    }

    /***** Helpers *****/
    private function preparePlainPassword($user)
    {
        if ($user->getPlainPassword()) {
            $user->setPlainPassword(
                $user->getPlainPassword(),
                $this->container->get('security.password_encoder')
            );
        }
    }
    
    private function preventLockingYourself($user)
    {
        if (
            $user === $this->getUser() &&
            $user->isLocked()
        ) {
            $user->unlock();

            $this->addFlash(
                'sonata_flash_error',
                'You can not lock yourself.'
            );
        }
    }
    
    private function preventDisablingYourself($user)
    {
        if (
            $user === $this->getUser() &&
            !$user->isEnabled()
        ) {
            $user->enable();

            $this->addFlash(
                'sonata_flash_error',
                'You can not disable yourself.'
            );
        }
    }

    private function addFlash($type, $message)
    {
        return $this->container
            ->get('session')
            ->getFlashBag()
            ->add($type, $message)
        ;
    }

    private function getUser()
    {
        return $this->container
            ->get('security.token_storage')
            ->getToken()
            ->getUser()
        ;
    }
}
