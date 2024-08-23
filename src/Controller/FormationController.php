<?php

namespace App\Controller;

use App\Entity\Cursus;
use App\Entity\Theme;
use App\Repository\CursusRepository;
use App\Repository\LessonsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ThemeRepository;

class FormationController extends AbstractController
{
    #[Route('/formation', name: 'app_formation')]
    public function index(ThemeRepository $themeRepository): Response
    {
        $themes = $themeRepository->findAll();
        return $this->render('formation/index.html.twig', [
            'themes'=>$themes,
        ]);
    }

    #[Route('/formation/{id}/cursus', name:'app_theme_cursus')]
    public function cursus(Theme $theme, CursusRepository $cursusRepository):Response
    {
        $cursus = $cursusRepository->findBy(['theme'=>$theme]);
        return $this->render('formation/cursus.html.twig', [
            'theme'=>$theme,
            'cursus'=>$cursus,
        ]);
    }

    #[Route('/formation/{themeId}/cursus/{cursusId}/lesson', name:'app_cursus_lesson')]
    public function lesson (int $cursusId, CursusRepository $cursusRepository ,LessonsRepository $lessonsRepository): Response
    {
        $cursus = $cursusRepository->find($cursusId);
        $lesson = $lessonsRepository->findBy(['cursus'=>$cursus]);
        return $this->render('formation/lesson.html.twig',[
            'cursus'=>$cursus,
            'lessons'=>$lesson
        ]);
    }

    #[Route('/formation/{themeId]/cursus/{cursusId}/lesson/{lessonId}', name:'app_lesson')]
    public function lessonDetail(int $lessonId, LessonsRepository $lessonsRepository): Response
    {
        $lesson = $lessonsRepository->find($lessonId);

        return $this->render('formation/lesson_detail.html.twig', [
            'lesson'=> $lesson,
        ]);
    }
}
