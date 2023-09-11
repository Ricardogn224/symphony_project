<?php

namespace App\Controller\Front;

use App\Entity\GiftList;
use App\Form\GiftListType;
use App\Repository\GiftListRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

#[Route('/gift_list')]
class GiftListController extends AbstractController
{
    #[Route('/', name: 'app_gift_list_index', methods: ['GET'])]
    public function index(GiftListRepository $giftListRepository): Response
    {
        #afficher toutes les listes de cadeaux qui sont publiques 
        if ($this->getUser()) {
            $user = $this->getUser();
            if (in_array('ROLE_ADMIN', $user->getRoles())) {
                return $this->render('front/gift_list/index.html.twig', [
                    'gift_lists' => $giftListRepository->findAll(),
                ]);
            } else {
                #aficher les listes crée par les utilisateurs 
                return $this->render('front/gift_list/index.html.twig', [
                    'gift_lists' => $giftListRepository->findBy(['createdBy' => $user]),
                ]);
            }
        }


        return $this->render('front/gift_list/index.html.twig', [
            // all gift lists with privacy set to false and is between the opening and closing dates

            'gift_lists' => $giftListRepository->findAvalaibleListGifts(),
        ]);
    }

    #[Route('/new', name: 'app_gift_list_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        #get current user
        $user = $this->getUser();

        # si l'utilisateur n'est pas connecter le rediriger vers la page de connection

        if (!$user) {
            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }


        $giftList = new GiftList();
        $giftList->setCreatedBy($user);
        $giftList->setStatus('active');
        $form = $this->createForm(GiftListType::class, $giftList);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($giftList);
            $entityManager->flush();

            return $this->redirectToRoute('front_app_gift_list_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('front/gift_list/new.html.twig', [
            'gift_list' => $giftList,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/show', name: 'app_gift_list_show', methods: ['GET', 'POST'])]
    public function show(GiftList $giftList, Request $request): Response
    {
        // Check if privacy is set to true
        if ($giftList->isPrivacy()) {
            // Create a form to capture the password
            $form = $this->createFormBuilder()
                ->add('password', PasswordType::class)
                ->getForm();

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $submittedPassword = $form->get('password')->getData();

                // Check if the submitted password matches the stored password
                if ($submittedPassword === $giftList->getPassword()) {
                    // Password is correct, display the gift list
                    $gifts = $giftList->getGifts();
                    return $this->render('front/gift_list/show.html.twig', [
                        'gift_list' => $giftList,
                        'gifts' => $gifts,
                    ]);
                } else {
                    // Password is incorrect, display an error message
                    $this->addFlash('error', 'Incorrect password.');
                }
            }

            // Display the password input form
            return $this->render('front/gift_list/password_form.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        // If privacy is not set to true, display the gift list directly
        $gifts = $giftList->getGifts();
        return $this->render('front/gift_list/show.html.twig', [
            'gift_list' => $giftList,
            'gifts' => $gifts,
        ]);
    }


    #[Route('/{id}/edit', name: 'app_gift_list_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, GiftList $giftList, EntityManagerInterface $entityManager): Response
    {
        //on verifie que l'utilisateur est connecté
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }

        // Check if the user is the owner of the gift list
        if ($giftList->getCreatedBy() !== $this->getUser()) {
            return $this->redirectToRoute('front_app_gift_list_index', [], Response::HTTP_SEE_OTHER);
        }

        $form = $this->createForm(GiftListType::class, $giftList);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('front_app_gift_list_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('front/gift_list/edit.html.twig', [
            'gift_list' => $giftList,
            'form' => $form,
        ]);
    }

    // on crée une route qui permet d'archiver une liste de cadeaux
    #[Route('/{id}/archive', name: 'app_gift_list_archive', methods: ['GET', 'POST'])]
    public function archive(Request $request, GiftList $giftList, EntityManagerInterface $entityManager): Response
    {
        //on verifie que l'utilisateur est connecté
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }

        // Check if the user is the owner of the gift list
        if ($giftList->getCreatedBy() !== $this->getUser()) {
            return $this->redirectToRoute('front_app_gift_list_index', [], Response::HTTP_SEE_OTHER);
        }

        // on change la valeur de l'attribut archived de la liste de cadeaux
        $giftList->setStatus('archived');
        $entityManager->flush();

        return $this->redirectToRoute('front_app_gift_list_index', [], Response::HTTP_SEE_OTHER);
    }


    #[Route('/{id}', name: 'app_gift_list_delete', methods: ['POST'])]
    public function delete(Request $request, GiftList $giftList, EntityManagerInterface $entityManager): Response
    {
        //on verifie que l'utilisateur est connecté
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }

        // Check if the user is the owner of the gift list
        if ($giftList->getCreatedBy() !== $this->getUser()) {

            // flash message
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à supprimer cette liste de cadeaux.');
            return $this->redirectToRoute('front_app_gift_list_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($this->isCsrfTokenValid('delete' . $giftList->getId(), $request->request->get('_token'))) {
            //first delete all gifts related to the gift list
            $gifts = $giftList->getGifts();
            foreach ($gifts as $gift) {
                $entityManager->remove($gift);
            }
            $entityManager->remove($giftList);
            $entityManager->flush();
        }





        return $this->redirectToRoute('front_app_gift_list_index', [], Response::HTTP_SEE_OTHER);
    }
}
