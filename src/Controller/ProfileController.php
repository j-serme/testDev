<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EditPasswordType;
use App\Form\EditProfileType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * @Route("/profile")
 */
class ProfileController extends AbstractController
{


    /**
     * @Route("/", name="profile")
     */
    public function index(): Response
    {


        return $this->render('profile/index.html.twig', [
            'controller_name' => 'ProfileController',
        ]);
    }

    /**
     * @Route("/change/{id}",name="profile_change")
     * @param User $user
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function change(User $user, Request $request, EntityManagerInterface $manager)
    {
        $user = $this->getUser();
        $form = $this->createForm(EditProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $user = $form->getData();

            $manager->persist($user);
            $manager->flush();

            $this->addFlash('notice', 'Les informations ont bien été mises à jour' );
            return $this->redirectToRoute('profile');
        }

        return $this->renderForm('profile/editInfos.html.twig', ['form' => $form]);

    }

    /**
     * @Route("/delete/{id}",name="profile_delete")
     * @param EntityManagerInterface $manager
     * @param User $user
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function delete(EntityManagerInterface $manager, User $user, SessionInterface $session)
    {

        if ($user === $this->getUser())
        {
            $manager->remove($user);
            $manager->flush();
            $session = new Session();
            $session->invalidate();

        }

        return $this->redirectToRoute('home_page');

    }

    /**
     * @Route("/change_password/{id}", name="profile_change_password")
     * @param User $user
     * @param EntityManagerInterface $manager
     * @param Request $request
     * @param \Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface $userPasswordHasher
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function changePassword(User $user, EntityManagerInterface $manager, Request $request, UserPasswordHasherInterface $userPasswordHasher)
    {
        $user = $this->getUser();
        $form = $this->createForm(EditPasswordType::class, $user);
        $form->handleRequest($request);

        if ($request->isMethod('POST'))
        {
            $oldPassword = $request->request->get('oldPassword');
            $newPassword = $request->request->get('newPassword');
            $newPasswordConfirmed = $request->request->get('newPasswordConfirmed');

            if ($oldPassword!=="" && $newPassword!=="" && $newPasswordConfirmed!=="")
            {
                $result = $userPasswordHasher->isPasswordValid($user, $oldPassword);

                if (!$result)
                {
                    $this->addFlash('notice', 'Le mot de passe actuel est erroné');
                } else {

                    if ($newPassword === $newPasswordConfirmed)
                    {
                        $user->setPassword($userPasswordHasher->hashPassword($user, $oldPassword));
                        $manager->persist($user);
                        $manager->flush();
                        $this->addFlash('notice', 'Mot de passe mis à jour');
                    } else {
                        $this->addFlash('notice', 'Les mots de passe ne sont pas identiques');
                        return $this->redirectToRoute('profile_change_password', ['id'=>$user->getId()]);
                    }
                }

            } else {
                $this->addFlash('notice', 'Tous les champs sont requis');
            }

            return $this->redirectToRoute('profile');




        }

        return $this->renderForm('profile/editPassword.html.twig', ['form' => $form]);

    }

}
