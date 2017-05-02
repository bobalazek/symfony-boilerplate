<?php

namespace AdminBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

// TODO: show deleted (red row) on soft-deleted users
/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class UserAdmin extends AbstractAdmin
{
    protected $roleChoices = [
        'Super admin' => 'ROLE_SUPER_ADMIN',
        'Admin' => 'ROLE_ADMIN',
        'User' => 'ROLE_USER',
    ];

    protected function configureFormFields(FormMapper $formMapper)
    {
        $subject = $this->getSubject();

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
                ->add('email', 'text')
                ->add('plainPassword', 'repeated', [
                    'required' => $subject->getId()
                        ? false
                        : true,
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
        ;
    }
}