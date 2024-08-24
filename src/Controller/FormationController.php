<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Cursus;
use App\Entity\Lessons;
use App\Entity\Theme;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CursusRepository;
use App\Repository\LessonsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ThemeRepository;
use App\Service\StripeService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FormationController extends AbstractController
{

    private EntityManagerInterface $entityManager;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/formation', name: 'app_formation')]
    public function index(ThemeRepository $themeRepository): Response
    {
        $themes = $themeRepository->findAll();
        return $this->render('formation/index.html.twig', [
            'themes'=>$themes,
        ]);
    }

    #[Route('/formation/{id}/cursus', name:'app_theme_cursus')]
    public function cursus(Theme $theme, CursusRepository $cursusRepository, LessonsRepository $lessonsRepository):Response
    {
        $cursusList = $cursusRepository->findBy(['theme'=>$theme]);
        $userHasPurchasedAllLessonsInCursus = [];

        if($this->getUser()){
            foreach($cursusList as $cursus){
                $lessons = $lessonsRepository->findBy(['cursus'=>$cursus]);
                $allLessonsPurchased = true;

                foreach($lessons as $lesson){
                    if(!$this->userHasPurchasedLesson($lesson)){
                        $allLessonsPurchased = false;
                        break;
                    }
                }
                $userHasPurchasedAllLessonsInCursus[$cursus->getId()] = $allLessonsPurchased;
            }
        }
        return $this->render('formation/cursus.html.twig',[
            'theme'=>$theme,
            'cursus'=>$cursusList,
            'userHasPurchasedAllLessonsInCursus'=> $userHasPurchasedAllLessonsInCursus,
        ]);
    }

    #[Route('/formation/{themeId}/cursus/{cursusId}/buy', name:'app_cursus_buy')]
    public function buyCursus(int $themeId, int $cursusId, CursusRepository $cursusRepository, StripeService $stripeService): Response
    {
        $cursus = $cursusRepository->find($cursusId);

        if(!$cursus){
            throw $this->createNotFoundException('Cursus not found');
        }
        if(!$this->getUser()){
            return $this->redirectToRoute('app_login');
        }

        $user = $this->getUser();

        if (!$user->getIsVerified()){
            $this->addFlash('error', 'Vous devez activer votre compte pour effectuer un achat');
            return $this->redirectToRoute('app_login');
        }

        $lineItems = [[
            'price_data'=>[
                'currency'=>'eur',
                'product_data'=>[
                    'name'=>$cursus->getName(),
                ],
                'unit_amount'=>$cursus->getPrix()*100,
            ],
            'quantity'=>1,
        ]];

        $successUrl = $this->generateUrl('app_cursus_success', ['cursusId'=>$cursusId], UrlGeneratorInterface::ABSOLUTE_URL);
        $cancelUrl = $this->generateUrl('app_cursus_cancel', ['cursusId'=>$cursusId], UrlGeneratorInterface::ABSOLUTE_URL);

        $sessionStripe = $stripeService->createCheckoutSession($lineItems, $successUrl, $cancelUrl);
        return $this->redirect($sessionStripe->url, 303);
    }

    #[Route('formation/{cursusId}/success', name:'app_cursus_success')]
    public function cursusSuccess(int $cursusId, CursusRepository $cursusRepository):Response
    {
        $cursus = $cursusRepository->find($cursusId);
        /** @var User $user */
        $user = $this->getUser();

        if($user && $cursus){
            foreach($cursus->getLessons() as $lesson){
                $user->addPurchasedLesson($lesson);
            }
            $this->entityManager->flush();
        }
        return $this->redirectToRoute('app_theme_cursus', ['id'=>$cursus->getTheme()->getId()]);
    }

    #[Route('/formation/{cursusId}/cancel', name:'app_cursus_cancel')]
    public function cursusCancel(int $cursusId):Response
    {
        $this->addFlash('error', 'Le paiement du cursus a été annulé.');
        return $this->redirectToRoute('app_theme_cursus', ['id'=>$cursusId]);
    }
    

    #[Route('/formation/{themeId}/cursus/{cursusId}/lesson', name:'app_cursus_lesson')]
    public function lesson (int $cursusId, CursusRepository $cursusRepository ,LessonsRepository $lessonsRepository): Response
    {
        $cursus = $cursusRepository->find($cursusId);
        $lessons = $lessonsRepository->findBy(['cursus'=>$cursus]);

        $userHasPurchasedLesson = [];
        if ($this->getUser()){
            foreach($lessons as $lesson){
                $userHasPurchasedLesson[$lesson->getId()] = $this->userHasPurchasedLesson($lesson);
            }
        }

        return $this->render('formation/lesson.html.twig',[
            'cursus'=>$cursus,
            'lessons'=>$lessons,
            'userHasPurchasedLesson'=> $userHasPurchasedLesson,
        ]);
    }

    #[Route('/formation/{themeId}/cursus/{cursusId}/lesson/{lessonId}', name:'app_lesson')]
    public function lessonDetail(int $themeId, int $cursusId, int $lessonId, LessonsRepository $lessonsRepository, StripeService $stripeService): Response
    {
        $lesson = $lessonsRepository->find($lessonId);

        $user = $this->getUser();

        if (!$user->getIsVerified()){
            $this->addFlash('error', 'Vous devez activer votre compte pour effectuer un achat');
            return $this->redirectToRoute('app_login');
        }

        if (!$this->userHasPurchasedLesson($lesson)){
            $lineItems = [[
                'price_data'=>[
                    'currency'=>'eur',
                    'product_data'=>[
                        'name'=>$lesson->getName(),
                    ],
                    'unit_amount'=>$lesson->getPrix() * 100,
                ],
                'quantity'=>1,
            ]];

            $successUrl = $this->generateUrl('app_lesson_success', ['themeId' => $themeId, 'cursusId' => $cursusId, 'lessonId' => $lessonId], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL);
            $cancelUrl = $this->generateUrl('app_lesson_cancel', ['themeId' => $themeId, 'cursusId' => $cursusId, 'lessonId' => $lessonId], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL);
    
            $sessionStripe = $stripeService->createCheckoutSession($lineItems, $successUrl, $cancelUrl);
            return $this->redirect($sessionStripe->url, 303);
        }
        return $this->render('formation/lesson_detail.html.twig',[
            'lesson'=>$lesson,
        ]);
    }

    private function userHasPurchasedLesson(Lessons $lesson): bool
    {
        /** @var User $user  */
        $user = $this->getUser();
        return $user ? $user->getPurchasedLessons()->contains($lesson) : false;
    }

    #[Route('/formation/{themeId}/cursus/{cursusId}/lesson/{lessonId}/success', name: 'app_lesson_success')]
    public function lessonSuccess(int $lessonId, LessonsRepository $lessonsRepository): Response
    {
        $lesson = $lessonsRepository->find($lessonId);
        /** @var User $user */
        $user = $this->getUser();

        if($user){
            $user->addPurchasedLesson($lesson);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_lesson',[
            'themeId'=>$lesson->getCursus()->getTheme()->getId(),
            'cursusId'=>$lesson->getCursus()->getId(),
            'lessonId'=>$lesson->getId(),
        ]);
    }

    #[Route('/formation/{themeId}/cursus/{cursusId}/lesson/{lessonId}/cancel', name:'app_lesson_cancel')]
    public function lessonCancel(int $lessonId, LessonsRepository $lessonsRepository): Response
    {

        $lesson = $lessonsRepository->find($lessonId);
        $this->addFlash('error', 'Le paiement a été annulé');
        return $this->redirectToRoute('app_lesson',[
            'themeId'=>$lesson->getCursus()->getTheme()->getId(),
            'cursusId'=>$lesson->getCursus()->getId(),
            'lessonId'=>$lesson->getId(),
        ]);
    }


}
