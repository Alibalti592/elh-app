<?php

namespace App\Controller\Site;

use App\Entity\Intro;
use App\Entity\Page;
use App\Services\UtilsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SiteController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly UtilsService $utilsService) {

    }

    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return $this->render('site/modules/page/home.twig', [
        ]);
    }

    #[Route('/mentions-legales', name: "mentions_legales")]
    public function mentions(): Response
    {
        $page = $this->entityManager->getRepository(Page::class)->findOneBy([
            'slug' => 'mentions'
        ]);
        return $this->render('site/modules/page/default.twig', [
            'content' =>  $this->utilsService->htmlDecode($page->getContent()),
            'title' => $page->getTitle(),
            'noindex' => true
        ]);
    }

    #[Route('/cgu', name: "cgu")]
    public function cgu(): Response
    {
        $page = $this->entityManager->getRepository(Page::class)->findOneBy([
            'slug' => 'cgu'
        ]);
        return $this->render('site/modules/page/default.twig', [
            'content' =>  $this->utilsService->htmlDecode($page->getContent()),
            'title' => $page->getTitle(),
            'noindex' => true
        ]);
    }


    #[Route('/get-intro-text', name: "text-intro")]
    public function getIntro(): Response
    {
        $jsonResponse = new JsonResponse();
        $intro = $this->entityManager->getRepository(Intro::class)->loadIntro();
        $content = 'Bienvenue sur Muslim Connect !';
        if(!is_null($intro)) {
            $content = $this->utilsService->htmlDecode($intro->getContent());
        }
        $jsonResponse->setData([
            'text' => $content
        ]);
        return $jsonResponse;
    }

}
