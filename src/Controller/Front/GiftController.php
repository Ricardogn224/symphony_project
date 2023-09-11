<?php

namespace App\Controller\Front;

use Goutte\Client;
use App\Entity\Gift;
use App\Form\GiftType;
use App\Entity\GiftList;
use App\Service\EmailService;
use App\Security\EmailVerifier;
use App\Repository\GiftRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;


#[Route('/gift')]
class GiftController extends AbstractController
{
    private EmailVerifier $emailVerifier;
    private EmailService $emailService;

    public function __construct(EmailVerifier $emailVerifier, EmailService $emailService)
    {
        $this->emailVerifier = $emailVerifier;
        $this->emailService = $emailService;
    }


    #[Route('/', name: 'app_gift_index', methods: ['GET'])]
    public function index(GiftRepository $giftRepository, Security $security): Response
    {
        // Récupérez l'utilisateur connecté
        $user = $security->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            // Récupérez les cadeaux créés par l'utilisateur connecté
            // Utilisateur non connecté en tant qu'administrateur, affichez tous les cadeaux
            $gifts = $giftRepository->findAll();
        } else {

            $gifts = $giftRepository->findBy(['createdBy' => $user]);
        }


        return $this->render('front/gift/index.html.twig', [
            'gifts' => $giftRepository->findAll(),
        ]);
    }

    #[Route('/add-gift-via-link/{id}', name: 'add_gift_via_link', methods: ['GET', 'POST'])]
    public function addGiftViaLink(Request $request, EntityManagerInterface $entityManager, GiftList $giftList, SluggerInterface $slugger): Response
    {
        $gift = new Gift();
        $form = $this->createFormBuilder()
            ->add('lien', TextType::class, [
                'label' => 'Lien du cadeau',
                'attr' => ['placeholder' => 'Saisissez le lien du cadeau'],
            ])
            ->add(
                "name",
                null,
                [
                    'constraints' => [
                        new Assert\NotBlank([
                            'message' => "Le champ 'nom' ne peut pas être vide.",
                        ]),
                    ],
                ]
            )
            ->add('email', null, [
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => "Le champ 'email' ne peut pas être vide.",
                    ]),
                    new Assert\Email([
                        'message' => "L'email '{{ value }}' n'est pas valide.",
                    ]),
                ],
            ])
            ->add('save', SubmitType::class, ['label' => 'Ajouter le cadeau'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Récupérez le lien soumis par le formulaire
            $link = $form->get('lien')->getData();

            // Utilisez Goutte pour extraire les informations du lien
            $client = new Client();

            //verifiez que le lien est valide
            try {
                $crawler = $client->request('GET', $link);
            } catch (\Throwable $th) {
                // flash message
                $this->addFlash('danger', 'Le lien que vous avez saisi n\'est pas valide');
                return $this->redirectToRoute('front_add_gift_via_link', ['id' => $giftList->getId()]);
            }
            $crawler = $client->request('GET', $link);
            //dd($crawler);
            $imageUrl = '';
            $imgSrc = '';

            // Extrayez l'image du produit
            // Select the li elements by their CSS classes
            $liElements = $crawler->filter('ul.list-none.w-full li.list-none.w-full.flex.justify-center');
            //dd($liElements);
            $imgSrc = '';
            // Loop through the selected li elements
            $imgSrc = $liElements->each(function (Crawler $liNode, $i) {

                // Check if the 'display' property is set to 'none' in the li element's style attribute
                $style = $liNode->attr('style');
                if (!($style && strpos($style, 'display: none;') !== false)) {
                    // The li element is visible

                    // Find the img element within the visible li element
                    $imgNode = $liNode->filter('img');


                    // Check if an img element was found
                    if ($imgNode->count() > 0) {
                        // Get the src attribute of the img element
                        $imgSrc = $imgNode->attr('src');
                        return $imgSrc;
                    }
                }
            });

            //dd($imgSrc);

            // Check if an image was found




            //dd($imageUrl);
            // add https://www.backmarket.fr/ to the image url at the beginning
            if (empty($imgSrc)) {
                // redirect to the add gift via link page
                $this->addFlash('danger', 'Le lien que vous avez saisi ne contient pas d\'image');
                return $this->redirectToRoute('add_gift_via_link', ['id' => $giftList->getId()]);
            }

            $imageUrl = 'https://www.backmarket.fr/' . $imgSrc[0];
            //dd($imageUrl);

            // Exemple d'extraction du titre de la page
            $pageTitle = $crawler->filter('.title-1')->text();
            //dd($pageTitle);

            //je verifie que le tire fait moins de 255 caractères
            if (strlen($pageTitle) > 255) {
                // je reduis la taille du titre
                $pageTitle = substr($pageTitle, 0, 20);
            }
            #dd($pageTitle);

            /* // Téléchargez l'image
            $imageContent = file_get_contents($imageUrl);

            // Générez un nom de fichier unique pour l'image
            $imageName = md5(uniqid()) . '.jpg';

            // Définissez le chemin du répertoire d'upload VichUploader
            $uploadDir = $this->getParameter('kernel.project_dir') . '/public/images/gifts';

            // Écrivez l'image téléchargée dans le répertoire d'upload
            file_put_contents($uploadDir . '/' . $imageName, $imageContent);

            // Remplissez le champ image de l'entité Gift avec le nom de fichier
            $gift->setImageName($imageName); */



            //essayer d' Extrayez le prix du produit
            try {
                $price = $crawler->filter('#__layout > div > div.min-h-screen.flex.flex-col > div.flex-grow.flex.flex-col > div > div.flex.justify-center.px-6.md\:px-7.bg-white.text-black.max-w-full.mb-7 > div > div > div.md\:w-2\/3.lg\:w-1\/2.lg\:flex-1.max-w-full > div > div > div.flex.flex-col.mb-3.md\:mb-1 > div > div.flex-grow.flex-row.hidden.items-center.justify-between.mb-5.md\:flex > div > div > div > div.flex.md\:flex-col.items-baseline.md\:items-end > h2 > div > div:nth-child(1)')->text();
            } catch (\Throwable $th) {
                $price = 0;
            }
            //dd('price', $price);

            /*  //si le prix est vide mets 0
            if (empty($price)) {
                $price = 0;
            } */
            //$price = preg_replace('/[^0-9]/', '', $price);
            $price = preg_replace('/€/', '', $price);
            $price = preg_replace('/,/', '.', $price);
            $price = floatval($price);

            //dd($price);

            //$price = intval($price);
            #dd($price);
            // Remplissez les champs du cadeau avec les données extraites

            $gift->setNom($pageTitle);
            $gift->setPrix($price);
            $gift->setGiftList($giftList);
            $gift->setName($form->get('name')->getData());
            $gift->setEmail($form->get('email')->getData());
            $gift->setStatus('RESERVER');
            $gift->setLienAchat($link);

            // Enregistrez le cadeau dans la base de données
            $entityManager->persist($gift);
            $entityManager->flush();

            // Redirigez l'utilisateur vers la liste de cadeaux
            return $this->redirectToRoute('front_app_gift_list_show', ['id' => $giftList->getId()]);
        }

        return $this->render('front/gift/add_via_link.html.twig', [
            'gift' => $gift,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/new/{id}', name: 'app_gift_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, GiftList $giftList, UploaderHelper $uploaderHelper, Security $security, UrlGeneratorInterface $urlGenerator): Response
    {
        $giftList = $entityManager->getRepository(GiftList::class)->find($giftList);
        $gift = new Gift();
        $form = $this->createForm(GiftType::class, $gift);
        $form->handleRequest($request);

        // Check if the user is authenticated before accessing their data
        if ($security->getUser()) {
            // Get the current user's ID
            $user = $security->getUser();
            $userId = $user->getId();
            $gift->setCreatedBy($userId);
            $gift->setStatus('RESERVER');
        }

        if ($form->isSubmitted() && $form->isValid()) {


            $gift->setGiftList($giftList);
            $entityManager->persist($gift);
            $entityManager->flush();

            // je recuppère l'utilisateur qui à crée la liste 
            $user = $giftList->getCreatedBy();

            //Envoyer un email au créateur de la liste de cadeaux
            $destinator = $user->getEmail();
            $htlmContent = $this->renderView('front/gift/confirmation_gift.html.twig', [
                'gift' => $gift,
                'giftList' => $giftList,
                'user' => $user,
            ]);

            $subject = 'Ajout d\'un cadeau à votre liste de cadeaux' . $giftList->getTitre();

            $this->emailService->sendVerificationEmail($destinator, $subject, $htlmContent);

            // j'envoie un mail à la personne qui à ajouté le cadeau à la liste

            // Generate the URL for the cancellation link
            $urlCancel = $urlGenerator->generate('front_app_gift_status', ['id' => $gift->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

            $destinator = $gift->getEmail();
            $htlmContent = $this->renderView('front/gift/remove_gift_mail.html.twig', [
                'gift' => $gift,
                'giftList' => $giftList,
                'user' => $this->getUser(),
                'urlCancel' => $urlCancel,
            ]);
            $subject = 'Gestion de la reservationb de votre cadeau' . $giftList->getTitre();

            $this->emailService->sendVerificationEmail($destinator, $subject, $htlmContent);


            return $this->redirectToRoute('front_app_gift_list_show', ['id' => $giftList->getId()]);
        }

        return $this->render('front/gift/new.html.twig', [
            'gift_list' => $giftList,
            'gift' => $gift,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_gift_show', methods: ['GET'])]
    public function show(Gift $gift): Response
    {
        return $this->render('front/gift/show.html.twig', [
            'gift' => $gift,
        ]);
    }
    // j'aimerai crée une route pour changer le status du cadeau
    #[Route('/{id}/status', name: 'app_gift_status', methods: ['POST', 'GET'])]
    public function status(Request $request, Gift $gift, EntityManagerInterface $entityManager): Response
    {
        //on crée un formulaaire pour demander à l'utilisateur de rentrer l'email qu'il à mis à la création du cadeau 
        $form = $this->createFormBuilder()
            ->add('email', null, [
                'label' => 'Entrez votre email que vous avez utilisé pour créer le cadeau',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => "Le champ 'email' ne peut pas être vide.",
                    ]),
                    new Assert\Email([
                        'message' => "L'email '{{ value }}' n'est pas valide.",
                    ]),
                ],
            ])
            ->add('save', SubmitType::class, ['label' => 'Changer le status du cadeau'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // je recupère l'email du formulaire
            $email = $form->get('email')->getData();

            // je verifie que l'email est bien celui du cadeau
            if ($email !== $gift->getEmail()) {
                // flash message
                $this->addFlash('danger', 'Vous ne pouvez pas changer le status de ce cadeau');

                return $this->redirectToRoute('front_app_gift_index', [], Response::HTTP_SEE_OTHER);
            }



            $gift->setStatus('DERESERVER');
            $entityManager->flush();

            // flash message
            $this->addFlash('success', 'Le status du cadeau à bien été changé');
            // jenvoie un mail au créateur de la liste pour l'informer que le cadeau à été déréservé

            // je recuppère l'utilisateur qui à crée la liste 
            $user = $gift->getGiftList()->getCreatedBy();
            $giftList = $gift->getGiftList();

            //Envoyer un email au créateur de la liste de cadeaux
            $destinator = $user->getEmail();
            $htlmContent = $this->renderView('front/gift/dereservation_gift.html.twig', [
                'gift' => $gift,
                'giftList' => $giftList,
                'user' => $user,
            ]);

            $subject = 'DERESERVATION DU CADEAU  : ' . $giftList->getTitre();

            $this->emailService->sendVerificationEmail($destinator, $subject, $htlmContent);



            // je redirige sur la page du cadeau
            return $this->redirectToRoute('front_app_gift_show', ['id' => $gift->getId()], Response::HTTP_SEE_OTHER);
        }



        //afficher le formulaire
        return $this->render('front/gift/status.html.twig', [
            'gift' => $gift,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_gift_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Gift $gift, EntityManagerInterface $entityManager): Response
    {
        //on verifie que l'utilisateur est connecté
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }

        // Check if the user is not  admin continue or redirect to gift list index
        if (!in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            // Check if the user is the owner of the gift list
            return $this->redirectToRoute('front_app_gift_index', [], Response::HTTP_SEE_OTHER);
        }

        $form = $this->createForm(GiftType::class, $gift);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('front_app_gift_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('front/gift/edit.html.twig', [
            'gift' => $gift,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_gift_delete', methods: ['POST', 'GET'])]
    public function delete(Request $request, Gift $gift, EntityManagerInterface $entityManager): Response
    {
        //on verifie que l'utilisateur est connecté
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }
        // Check if the user is the owner of the gift list
        if ($gift->getGiftList()->getCreatedBy()->getId() !== $this->getUser()->getId()) {
            //dd($gift->getGiftList()->getCreatedBy(), $this->getUser());
            // flash message
            $this->addFlash('danger', 'Vous ne pouvez pas supprimer ce cadeau');

            return $this->redirectToRoute('front_app_gift_index', [], Response::HTTP_SEE_OTHER);
        }
        // je récupère l'id de la liste auqeu appartient le cadeau
        $giftListId = $gift->getGiftList()->getId();

        if ($this->isCsrfTokenValid('delete' . $gift->getId(), $request->request->get('_token'))) {
            $entityManager->remove($gift);
            $entityManager->flush();
        }

        //je redirige vers la liste de cadeau
        return $this->redirectToRoute('front_app_gift_list_show', ['id' => $giftListId], Response::HTTP_SEE_OTHER);

        //return $this->redirectToRoute('front_app_gift_index', [], Response::HTTP_SEE_OTHER);
    }
}
