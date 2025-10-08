<?php

namespace App\Controller\Api;

use App\Entity\Intro;
use App\Entity\Location;
use App\Entity\Don;
use App\Services\CRUDService;
use App\Services\UtilsService;
use App\UIBuilder\DonUI;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DonController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly UtilsService $utilsService,
                                private readonly CRUDService $CRUDService, private readonly DonUI $donUI) {}

    #[Route('/load-dons')]
    public function loadList(Request $request): Response
    {
        $dons = $this->entityManager->getRepository(Don::class)
            ->findAll();
        $donUIs = [];
        foreach ($dons as $don) {
            $donUIs[] = $this->donUI->getDon($don);
        }
        $intro = $this->entityManager->getRepository(Intro::class)->loadIntro('don');
        $content = $this->utilsService->htmlDecode($intro->getContent());
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'dons' => $donUIs,
            'intro' => $content
        ]);
        return $jsonResponse;
    }
}
