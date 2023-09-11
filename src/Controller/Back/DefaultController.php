<?php

namespace App\Controller\Back;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/')]
class DefaultController extends AbstractController
{
    #[Route('/', name: 'app_default')]
    public function index(): Response
    {
        //on verifie si l'utilisateur est connecté
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }
        //on verifie si l'utilisateur est un administrateur
        if (!in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            return $this->render('back/default.html.twig', []);
        }
        //on redirige vers la page d'administration
        return $this->render('back/default.html.twig', []);
    }
}
