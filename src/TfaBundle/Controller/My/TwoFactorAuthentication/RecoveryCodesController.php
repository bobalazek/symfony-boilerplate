<?php

namespace TfaBundle\Controller\My\TwoFactorAuthentication;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\User;
use AppBundle\Entity\UserRecoveryCode;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class RecoveryCodesController extends Controller
{
    /**
     * @Route("/my/tfa/recovery-codes", name="my.tfa.recovery_codes")
     * @Security("has_role('ROLE_USER')")
     */
    public function recoveryCodesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $actionsResponse = $this->handleActions(
            $request,
            $user,
            $em
        );
        if ($actionsResponse) {
            return $actionsResponse;
        }

        $query = $em->createQueryBuilder()
            ->select('urc')
            ->from('AppBundle:UserRecoveryCode', 'urc')
            ->where('urc.user = ?1')
            ->andWhere('urc.deletedAt IS NULL')
            ->setParameter(1, $user)
        ;

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10,
            [
                'defaultSortFieldName' => 'urc.createdAt',
                'defaultSortDirection' => 'desc',
            ]
        );

        return $this->render(
            'TfaBundle:Content:my/tfa/recovery_codes.html.twig',
            [
                'pagination' => $pagination,
            ]
        );
    }

    /**
     * @param Request       $request
     * @param User          $user
     * @param EntityManager $em
     */
    protected function handleActions(Request $request, User $user, EntityManager $em)
    {
        $action = $request->query->get('action');
        if ($action === 'generate') {
            $userRecoveryCodes = $user->getUserRecoveryCodes(true);
            foreach ($userRecoveryCodes as $userRecoveryCode) {
                $em->remove($userRecoveryCode);
            }

            $user->prepareUserRecoveryCodes(
                $this->getParameter('recovery_codes_count')
            );

            $em->persist($user);
            $em->flush();

            $this->addFlash(
                'success',
                $this->get('translator')->trans(
                    'my.tfa.recovery_codes.regenerate.flash_message.text'
                )
           );

            return $this->redirectToRoute('my.tfa.recovery_codes');
        }

        return null;
    }
}
