<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

#[Route('/manage-password')]
class ManagePasswordController extends AbstractController
{

    private EmailVerifier $emailVerifier;
    private EmailService $emailService;

    public function __construct(EmailVerifier $emailVerifier, EmailService $emailService)
    {
        $this->emailVerifier = $emailVerifier;
        $this->emailService = $emailService;
    }

    #[Route(path: '/', name: 'app_default_manage_password')]
    public function defaultManagePassword(AuthenticationUtils $authenticationUtils): Response
    {
        
        return $this->render('security/manage-password-default.html.twig', []);
    }

    #[Route(path: '/check-email', name: 'app_check_email')]
    public function checkEmail(Request $request, UserRepository $userRepository, VerifyEmailHelperInterface $verifyEmailHelper): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
    
            // Check if the email exists in the user table
            $user = $userRepository->findOneBy(['email' => $email]);
    
            if ($user) {

                $signatureComponents = $verifyEmailHelper->generateSignature(
                    'app_verify_email_forgot_password',
                    $user->getId(),
                    $user->getEmail(),
                    ['id' => $user->getId()]
                );

                $destinator = $user->getEmail();
                $htlmContent = $this->renderView('security/manage-credentials/confirmation_reset_password.html.twig') . '<a href=' . $signatureComponents->getSignedUrl() . '>Réinitialiser</a>';
                $subject = 'Réinitialisation du mot de passe';

                $this->emailService->sendVerificationEmail($destinator, $subject, $htlmContent);

                return $this->render('security/manage-credentials/forgot-password-template.html.twig', [
                ]);
            }else{
                $this->addFlash('failure', 'Email incorrecte');
                return $this->redirectToRoute('app_login');
            }
        }
    
        return $this->render('security/manage-credentials/check-email.html.twig', []);
    }

    #[Route('/verify/email', name: 'app_verify_email_forgot_password')]
    public function verifyUserEmail(Request $request, UserRepository $userRepository): Response
    {
        $id = $request->get('id');

        if (null === $id) {
            return $this->redirectToRoute('app_register');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('app_register');
        }

        // validate email confirmation link
        try {
            $this->emailVerifier->handleEmailConfirmationPassword($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $exception->getReason());

            return $this->redirectToRoute('app_login');
        }
        
        return $this->redirectToRoute('app_reset_password_forgot', array('id' => $id));

        //return $this->render('security/manage-credentials/reset-password-forgot.html.twig', ['email' => $user->getEmail(), 'id' => $user->getId()]);
    }


    #[Route('/reset-password-forgot/{id}', name: 'app_reset_password_forgot')]
    public function resetPasswordForgot(User $user, Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        if ($request->isMethod('POST')) {
            $newPassword = $request->request->get('password');

            if (!$user) {
                $this->addFlash('failure', 'Votre mot de passe n\'a pu être réinitialisé');
                return $this->redirectToRoute('app_login');
            } else {
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $newPassword
                    )
                );

                $entityManager->persist($user);
                $entityManager->flush();

                // Redirect to a success page or login page
                // Show a flash message indicating successful password reset
                $this->addFlash('success', 'Votre mot de passe a été réinitialisé avec succès');
                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('security/manage-credentials/reset-password-forgot.html.twig', ['email' => $user->getEmail(), 'id' => $user->getId()]);

        
    }

}

