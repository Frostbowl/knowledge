<?php

namespace App\Controller;

use App\Entity\Lessons;
use App\Entity\Cursus;
use App\Entity\Theme;
use App\Form\CursusType;
use App\Form\ThemeType;
use App\Form\LessonsType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

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

    #[Route('admin/cursus', name:'admin_create_cursus')]
    public function createCursus(Request $request, EntityManagerInterface $entityManager): Response
    {
        $cursus = new Cursus();
        $form = $this->createForm(CursusType::class, $cursus);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $cursus->setCreatedAt(new \DateTimeImmutable());
            $cursus->setCreatedBy($this->getUser());

            $entityManager->persist($cursus);
            $entityManager->flush();

            $this->addFlash('success', 'Le cursus à été créé avec succès.');

            return $this->redirectToRoute('app_admin');
        }
        return $this->render('admin/create_cursus.html.twig', [
            'form'=>$form->createView(),
        ]);
    }

    #[Route('admin/lesson', name:'admin_create_lesson')]
    public function createLesson(Request $request, EntityManagerInterface $entityManager,SluggerInterface $slugger): Response
    {
        $lesson = new Lessons();
        $form = $this->createForm(LessonsType::class, $lesson);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){

            $videoFile = $form->get('videoFile')->getData();
            if($videoFile){
                $originalFilename = pathinfo($videoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$videoFile->guessExtension();

                try{
                    $videoFile->move(
                        $this->getParameter('kernel.project_dir').'/public/uploads/videos',
                        $newFilename
                    );
                } catch(FileException $e){

                }

                $lesson->setVideoFile($newFilename);
            }
            
            $lesson->setCreatedAt(new \DateTimeImmutable());
            $lesson->setCreatedBy($this->getUser());

            $entityManager->persist($lesson);
            $entityManager->flush();

            $this->addFlash('success', 'La leçon a bien été créée');
            return $this->redirectToRoute('app_admin');
        }

        return $this->render('admin/create_lesson.html.twig', [
            'form'=>$form->createView(),
        ]);
    }
}
