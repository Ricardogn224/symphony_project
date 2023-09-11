<?php

namespace App\Controller\Front;



use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'app_default')]
    public function index(Request $request): Response
    {

        return $this->render('front/home.html.twig', [
        ]);
    }

    #[Route('/termine', name: 'app_default_finish')]
    public function test(Request $request): Response
    {
        return $this->render('front/default/finish.html.twig');
    }
}
