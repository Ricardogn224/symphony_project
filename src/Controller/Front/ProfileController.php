<?php

namespace App\Controller\Front;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Form\UserCredentialsType;
use App\Form\UserEditType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/profile')]
class ProfileController extends AbstractController
{
    #[Route('/', name: 'app_profile_default')]
    public function index(Request $request): Response
    {
        return $this->render('front/profile/default.html.twig', []);
    }

    #[Route('/edit-user', name: 'app_profile_edit_user')]
    public function editUser(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(UserEditType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('front_app_profile_default');
        }

        return $this->render('front/profile/edit-user.html.twig', [
            'editUserForm' => $form->createView(),
        ]);
    }

    #[Route('/edit-credentials', name: 'app_profile_edit_credentials')]
    public function editCredentials(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserCredentialsType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user1 = $this->getUser();
            $user1->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $entityManager->persist($user1);
            $entityManager->flush();

            return $this->redirectToRoute('front_app_profile_default');
        }

        return $this->render('front/profile/edit-credentials.html.twig', [
            'editCredentialsForm' => $form->createView(),
        ]);
    }

    #[Route('/termine', name: 'app_default_finish')]
    public function test(Request $request): Response
    {
        return $this->render('front/default/finish.html.twig');
    }
}