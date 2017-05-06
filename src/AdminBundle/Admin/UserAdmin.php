<?php

namespace AdminBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use AppBundle\Entity\User;
use AppBundle\AppBundle;
use AppBundle\Form\Type\ProfileType;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class UserAdmin extends AbstractAdmin
{
    use ContainerAwareTrait;

    /***** Configuration *****/

    protected $datagridValues = [
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt',
    ];

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $user = $this->getSubject();

        $formMapper
            ->with('Profile', ['class' => 'col-md-4'])
                ->add('profile', ProfileType::class, [
                    'label' => false,
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
                    'choices' => User::$rolesAvailable,
                ])
            ->end()
            ->with('Statuses', ['class' => 'col-md-4'])
                ->add('enabled')
                ->add('verified')
                ->add('warned')
                ->add('warnedReason')
                ->add('locked')
                ->add('lockedReason')
            ->end()
        ;
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
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

    /**
     * @param ListMapper $listMapper
     */
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
                'choices' => User::$rolesAvailable,
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

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
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
                ->add('profile.gender', null, [
                    'label' => 'Gender',
                ])
                ->add('profile.birthday', 'date', [
                    'label' => 'Birthday',
                ])
            ->end()
            ->with('Account', ['class' => 'col-md-4'])
                ->add('username')
                ->add('email')
                ->add('newEmail')
                ->add('lastActiveAt')
                ->add('activatedAt')
                ->add('updatedAt')
                ->add('createdAt')
                ->add('deletedAt')
            ->end()
            ->with('Roles', ['class' => 'col-md-4'])
                ->add('roles', 'choice', [
                    'multiple' => true,
                    'expanded' => true,
                    'choices' => User::$rolesAvailable,
                ])
            ->end()
            ->with('Statuses', ['class' => 'col-md-4'])
                ->add('enabled')
                ->add('verified')
                ->add('warned')
                ->add('warnedReason')
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

    /**
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('impersonate', $this->getRouterIdParameter().'/impersonate');
        $collection->add('restore', $this->getRouterIdParameter().'/restore');
    }

    /***** Hooks *****/

    /**
     * @param AppBundle\Entity\User $user
     */
    public function prePersist($user)
    {
        $this->preparePlainPassword($user);
    }

    /**
     * @param AppBundle\Entity\User $user
     */
    public function preUpdate($user)
    {
        $this->preparePlainPassword($user);
        $this->preventLockingYourself($user);
        $this->preventLockingWithoutReason($user);
        $this->preventDisablingYourself($user);
        // TODO: figure out a way to prevent removing roles from from users with higher roles,
        // for example: user A has ROLE_ADMIN and tries to remove the ROLE_SUPER_ADMIN from user B.
    }

    /***** Helpers *****/

    /**
     * @param AppBundle\Entity\User $user
     */
    private function preparePlainPassword($user)
    {
        if ($user->getPlainPassword()) {
            $user->setPlainPassword(
                $user->getPlainPassword(),
                $this->container->get('security.password_encoder')
            );
        }
    }

    /**
     * @param AppBundle\Entity\User $user
     */
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

    /**
     * @param AppBundle\Entity\User $user
     */
    private function preventLockingWithoutReason($user)
    {
        if (
            $user->isLocked() &&
            empty($user->getLockedReason())
        ) {
            $user->unlock();

            $this->addFlash(
                'sonata_flash_error',
                'You can not lock someone without entering a reason.'
            );
        }
    }

    /**
     * @param AppBundle\Entity\User $user
     */
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

    /**
     * @param string $type
     * @param string $message
     */
    private function addFlash($type, $message)
    {
        return $this->container
            ->get('session')
            ->getFlashBag()
            ->add($type, $message)
        ;
    }

    /**
     * @return AppBundle\Entity\User
     */
    private function getUser()
    {
        return $this->container
            ->get('security.token_storage')
            ->getToken()
            ->getUser()
        ;
    }

    /**
     * @return Doctrine\ORM\EntityManager
     **/
    private function getDoctrineEntityManager()
    {
        return $this->container
            ->get('doctrine')->getEntityManager()
        ;
    }
}
