<?php

namespace App\Controller;

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
}
