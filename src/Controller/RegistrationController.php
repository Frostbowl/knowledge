<?php

namespace App\Controller;

use App\Service\SendMailService;
use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security as SecurityBundleSecurity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Guard\AuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use App\Security\UserAuthenticator;

class RegistrationController extends AbstractController
{
    private $sendMailService;
    public function __construct(SendMailService $sendMailService)
    {
        $this->sendMailService = $sendMailService;
    }

    #[Route('/register', name:'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()&& $form->isValid()){
            $user->setRoles(['ROLE_USER']);
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $user->setConfirmationToken(Uuid::v4()->toRfc4122());

            $entityManager->persist($user);
            $entityManager->flush();

            $this->sendMailService->send(
                'no-reply@knowledge.com',
                $user->getEmail(),
                'Confirmation d\'inscription',
                'registration_confirmation',
                [
                    'user'=>$user,
                    'confirmationUrl'=>$this->generateUrl('app_verify_email', [
                        'token'=>$user->getConfirmationToken()
                    ], UrlGeneratorInterface::ABSOLUTE_URL)
                ]
            );
            return $this->render('registration/waiting.html.twig', [
                'user'=>$user
            ]);
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm'=> $form->createView(),
        ]);
    }

    #[Route('/wait-validation', name:'app_wait_validation')]
    public function waitingValidation():Response
    {
        return $this->render('registration/waiting.html.twig');
    }

    #[Route('/verify-email/{token}', name:'app_verify_email')]
    public function verifyEmail(string $token, EntityManagerInterface $entityManager, UserAuthenticatorInterface $userAuthenticator, UserAuthenticator $authenticator, Request $request): Response
    {
        $user = $entityManager->getRepository(User::class)->findOneBy(['confirmationToken'=>$token]);

        if (!$user){
            throw $this->createNotFoundException('Le lien de validation est invalide');
        }
        $user->setIsVerified(true);
        $user->setConfirmationToken(null);

        $entityManager->flush();

        $this->addFlash('Succes', 'Email vérifié avec succès');

        return $userAuthenticator->authenticateUser(
            $user,
            $authenticator,
            $request
        );
    }
}
