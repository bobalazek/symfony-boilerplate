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
            ->with('Profile')
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
            ->with('Account')
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
            ->with('Roles')
                ->add('roles', 'choice', [
                    'label' => false,
                    'multiple' => true,
                    'expanded' => true,
                    'choices' => $this->roleChoices,
                ])
            ->end()
            ->with('Statuses')
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
                        'template' => 'AdminBundle:CRUD:list__action_impersonate.html.twig'
                    ],
                    'restore' => [
                        'template' => 'AdminBundle:CRUD:list__action_restore.html.twig'
                    ],
                ],
            ])
        ;
    }

    public function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->with('Profile')
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
            ->with('Account')
                ->add('username')
                ->add('email')
            ->end()
            ->with('Roles')
                ->add('roles', 'choice', [
                    'multiple' => true,
                    'expanded' => true,
                    'choices' => $this->roleChoices,
                ])
            ->end()
            ->with('Statuses')
                ->add('enabled')
                ->add('verified')
                ->add('warned')
                ->add('locked')
                ->add('lockedReason')
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
        $this->preUpdate($user);
    }

    public function preUpdate($user)
    {
        if ($user->getPlainPassword()) {
            $user->setPlainPassword(
                $user->getPlainPassword(),
                $this->container->get('security.password_encoder')
            );
        }
    }
}
