<?php

namespace App\Controller\Api;

use App\Entity\CarteShare;
use App\Entity\Mosque;
use App\Entity\Carte;
use App\Entity\NavPageContent;
use App\Entity\Relation;
use App\Entity\User;
use App\Services\CRUDService;
use App\Services\NotificationService;
use App\Services\S3Service;
use App\Services\UtilsService;
use App\UIBuilder\CarteUI;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PageController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly UtilsService $utilsService, private readonly S3Service $s3Service) {}

    #[Route('/load-page-content')]
    #[Route('/load-page-content')]
public function loadPageContent(Request $request): Response
{
    $pageSlug = $request->get('page');

    if (!$pageSlug) {
        return new JsonResponse(['error' => 'Page not specified'], 400);
    }

    $page = $this->entityManager->getRepository(NavPageContent::class)->findOneBy([
        'slug' => $pageSlug
    ]);

    if (!$page) {
        return new JsonResponse(['error' => 'Page not found'], 404);
    }

    $image = $page->getImage();
    $imgsrc = null;
    if(!is_null($image)) {
        $imgsrc = $this->s3Service->getURLFromMedia($image);
    }

    $content = $page->getContent();
    if(is_null($content) || strlen($content) == 0) {
        $content = null;
    } else {
        $content = $this->utilsService->htmlDecode($content);
    }

    $pageUI = [
        'id' => $page->getId(),
        'content' => $content,
        'image'  => $imgsrc,
        'video'  => $page->getVideo(),
    ];

    return new JsonResponse([
        'page' => $pageUI,
        'showDetteInfos' => ($this->getUser() instanceof User) ? $this->getUser()->isShowDetteInfos() : false
    ]);
}

    #[Route('/hasseedetteinfos', methods: ['POST'])]
    public function hasSeeUpd(Request $request): Response
    {
        $currentUser = $this->getUser();
        if($currentUser instanceof User) {
            $currentUser->setShowDetteInfos(false);
            $this->entityManager->persist($currentUser);
            $this->entityManager->flush();
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([]);
        return $jsonResponse;
    }
}
