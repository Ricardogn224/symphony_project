<?php

namespace App\Controller\Back;

use App\Entity\User;
use App\Form\UserEditType;
use App\Repository\UserRepository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Security;

#[Route('/users-manage')]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_users')]
    public function index(UserRepository $userRepository, Security $security): Response
    {
        // je recupèrre l'utilisateur connecter 
        $user = $security->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/user/users.html.twig', [
            'users' => $userRepository->findAll()
        ]);
    }

    #[Route('/edit-level/{id}/{slug_role}', name: 'app_users_edit_level')]
    public function editLevel(User $user, string $slug_role, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $user->setRoles([$slug_role]); // Notice the use of square brackets to create an array.

        $entityManager->persist($user);
        $entityManager->flush();

        // Return a response, like a redirect or a JSON response, depending on your needs.
        $arr = [
            "succeed" => 'yes'
        ];

        return new JsonResponse($arr);
    }

    #[Route('/edit-user/{id}', name: 'app_admin_edit_user')]
    public function adminEditUser(User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$user) {
            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }
        // si l'utilisateur n'est pas connecter en tant qu'administrateur et que l'id de l'utilisateur connecter est différent de l'id de l'utilisateur a modifier
        if (!in_array('ROLE_ADMIN', $user->getRoles()) && $user->getId() != $this->getUser()->getId()) {
            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }
        $form = $this->createForm(UserEditType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérez ici la logique de modification de l'utilisateur en fonction des données du formulaire
            // Vous pouvez utiliser $user pour accéder aux données du formulaire

            $userRoles = $user->getRoles();
            $newRoles = $form->get('roles')->getData();
            $user->setRoles($newRoles);

            // Enregistrez les modifications dans la base de données
            $entityManager->flush();

            // Redirigez vers la page d'administration ou affichez un message de succès
            $this->addFlash('success', 'Les informations de l\'utilisateur ont été mises à jour avec succès.');

            return $this->redirectToRoute('back_app_users');
        }

        return $this->render('back/user/admin_edit_user.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    # on vas crée une route pour afficher les listes par utilisateur
    #[Route('/user-lists/{id}', name: 'app_user_lists')]
    public function userLists(User $user, UserRepository $userRepository): Response
    {
        return $this->render('back/user/user_lists.html.twig', [
            'user' => $user,
            'gift_lists' => $user->getGiftLists()
        ]);
    }
}
