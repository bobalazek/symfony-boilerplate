<?php

namespace AdminBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class UserBlockedActionAdmin extends AbstractAdmin
{
    use ContainerAwareTrait;

    /***** Configuration *****/

    protected $datagridValues = [
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt',
    ];

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('user.username', null, [
                'label' => 'Username',
            ])
            ->add('user.email', null, [
                'label' => 'Email',
            ])
            ->add('user.profile.firstName', null, [
                'label' => 'First name',
            ])
            ->add('user.profile.lastName', null, [
                'label' => 'Last name',
            ])
            ->add('action')
            ->add('ip')
            ->add('userAgent')
            ->add('sessionId')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('user.username', null, [
                'label' => 'Username',
            ])
            ->add('user.email', null, [
                'label' => 'Email',
            ])
            ->add('action')
            ->add('ip')
            ->add('userAgent')
            ->add('sessionId')
            ->add('createdAt')
            ->add('expiresAt')
            ->add('deletedAt')
            ->add('_action', 'actions', [
                'actions' => [
                    'show' => [],
                ],
            ])
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->with('General', ['class' => 'col-md-6'])
                ->add('action')
                ->add('ip')
                ->add('userAgent')
                ->add('sessionId')
                ->add('createdAt')
                ->add('expiresAt')
                ->add('deletedAt')
            ->end()
            ->with('User', ['class' => 'col-md-6'])
                ->add('user.id', null, [
                    'label' => 'ID',
                ])
                ->add('user.username', null, [
                    'label' => 'Username',
                ])
                ->add('user.email', null, [
                    'label' => 'Email',
                ])
                ->add('user.profile.firstName', null, [
                    'label' => 'First name',
                ])
                ->add('user.profile.lastName', null, [
                    'label' => 'Last name',
                ])
            ->end()
            ->with('User Agent', ['class' => 'col-md-6'])
                ->add('userAgentData', 'html_template', [
                    'label' => 'Data',
                    'html' => "{% include 'AdminBundle:Shared:show__user_agent_table.html.twig' %}",
                ])
            ->end()
            ->with('IP', ['class' => 'col-md-6'])
                ->add('ipData', 'html_template', [
                    'label' => 'Data',
                    'html' => "{% include 'AdminBundle:Shared:show__ip_table.html.twig' %}",
                ])
            ->end()
        ;
    }

    /**
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(['list', 'show']);
    }
}
