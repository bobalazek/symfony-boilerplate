<?php

namespace AdminBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use AppBundle\Entity\User;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class UserActionAdmin extends AbstractAdmin
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
            ->add('key', null, [
                'label' => 'Key',
            ])
            ->add('message', null, [
                'label' => 'Message',
            ])
            ->add('ip', null, [
                'label' => 'IP',
            ])
            ->add('userAgent', null, [
                'label' => 'User Agent',
            ])
            ->add('sessionId', null, [
                'label' => 'Session ID',
            ])
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
            ->add('key')
            ->add('message')
            ->add('createdAt')
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
                ->add('key', null, [
                    'label' => 'Key',
                ])
                ->add('message', null, [
                    'label' => 'Message',
                ])
                ->add('ip', null, [
                    'label' => 'IP',
                ])
                ->add('userAgent', null, [
                    'label' => 'User Agent',
                ])
                ->add('sessionId', null, [
                    'label' => 'Session ID',
                ])
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
                    'html' => "{% include 'AdminBundle:UserAction:show__user_agent_table.html.twig' %}",
                ])
            ->end()
            ->with('IP', ['class' => 'col-md-6'])
                ->add('ipData', 'html_template', [
                    'label' => 'Data',
                    'html' => "{% include 'AdminBundle:UserAction:show__ip_table.html.twig' %}",
                ])
            ->end()
            ->with('Data')
                ->add('data', 'html_template', [
                    'html' => "{% include 'AdminBundle:UserAction:show__data_table.html.twig' %}",
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
