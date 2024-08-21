<?php

namespace App\Controller;

use App\Entity\Theme;
use App\Form\ThemeType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/admin/theme', name: "admin_create_theme")]
    public function createTheme(Request $request, EntityManagerInterface $entityManager): Response
    {
        $theme = new Theme();
        $form = $this->createForm(ThemeType::class, $theme);

        $form->handleRequest($request);

        if( $form->isSubmitted() && $form->isValid()){

            $theme->setCreatedAt(new \DateTimeImmutable());
            $theme->setCreatedBy($this->getUser());
            $user = $this->getUser();
            if ($user instanceof UserInterface){
                $theme->setCreatedBy($user);
            }

            $entityManager->persist($theme);
            $entityManager->flush();

            $this->addFlash('succes', 'Nouveau theme créé');

            return $this->redirectToRoute('app_admin');
        }

        return $this->render('admin/create_theme.html.twig', [
            'form'=> $form->createView(),
        ]);
    }
}
