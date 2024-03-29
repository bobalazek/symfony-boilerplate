<?php

namespace AdminBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use CoreBundle\Entity\User;
use CoreBundle\CoreBundle;
use CoreBundle\Form\Type\ProfileType;

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
                ->add('username', TextType::class)
                ->add('email', EmailType::class)
                ->add('plainPassword', RepeatedType::class, [
                    'required' => $user->getId()
                        ? false
                        : true,
                    'type' => PasswordType::class,
                    'first_options' => [
                        'label' => 'Password',
                    ],
                    'second_options' => [
                        'label' => 'Repeat password',
                    ],
                ])
                ->add('mobile', PhoneNumberType::class, [
                    'widget' => PhoneNumberType::WIDGET_SINGLE_TEXT,
                    'preferred_country_choices' => ['DE', 'AT', 'CH'],
                ])
                ->add('locale', LocaleType::class, [
                    'choices' => array_flip($this->container->getParameter('locales')),
                    'choice_loader' => null,
                ])
            ->end()
            ->with('Roles', ['class' => 'col-md-4'])
                ->add('roles', ChoiceType::class, [
                    'label' => false,
                    'multiple' => true,
                    'expanded' => true,
                    'choices' => array_flip(User::$rolesAvailable),
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
            ->add('roles', 'html_template', [
                'label' => 'Roles',
                'html' => "{{ value | join(', ') }}",
            ])
            ->add('enabled', 'boolean', [
                'editable' => true,
            ])
            ->add('verified', 'boolean', [
                'editable' => true,
            ])
            ->add('warned', 'boolean', [
                'editable' => true,
            ])
            ->add('locked', 'boolean', [
                'editable' => true,
            ])
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
                ->add('mobile')
                ->add('locale')
            ->end()
            ->with('Timestamps', ['class' => 'col-md-4'])
                ->add('lastActiveAt')
                ->add('emailActivatedAt')
                ->add('mobileActivatedAt')
                ->add('updatedAt')
                ->add('createdAt')
                ->add('deletedAt')
            ->end()
            ->with('Roles', ['class' => 'col-md-4'])
                ->add('roles', 'html_template', [
                    'label' => 'Roles',
                    'html' => "{{ value | join(', ') }}",
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
                    'label' => 'Actions',
                    'html' => "{% include 'AdminBundle:User:show__user_actions_table.html.twig' %}",
                ])
            ->end()
        ;
    }

    /**
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('impersonate', $this->getRouterIdParameter() . '/impersonate');
        $collection->add('restore', $this->getRouterIdParameter() . '/restore');
    }

    /***** Hooks *****/

    /**
     * @param CoreBundle\Entity\User $user
     */
    public function prePersist($user)
    {
        $user->prepareUserRecoveryCodes(
            $this->container->getParameter('recovery_codes_count')
        );
        $this->preparePlainPassword($user);
    }

    /**
     * @param CoreBundle\Entity\User $user
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
     * @param CoreBundle\Entity\User $user
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
     * @param CoreBundle\Entity\User $user
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
     * @param CoreBundle\Entity\User $user
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
     * @param CoreBundle\Entity\User $user
     */
    private function preventDisablingYourself($user)
    {
        if (
            $user === $this->getUser() &&
            false === $user->isEnabled()
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
     * @return CoreBundle\Entity\User
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
