<?php

namespace App\Controller\Api;

use App\Entity\Jeun;
use App\Entity\Location;
use App\Entity\Obligation;
use App\Entity\Relation;
use App\Entity\Testament;
use App\Entity\TestamentShare;
use App\Entity\User;
use App\Services\CRUDService;
use App\Services\FcmNotificationService;
use App\Services\PdfGeneratorService;
use App\Services\UtilsService;
use App\UIBuilder\ObligationUI;
use App\UIBuilder\RelationUI;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

class JeunController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager) {}

    #[Route('/get-jeun')]
    public function loadJeun(Request $request): Response {
        $currentUser = $this->getUser();
        $jeun = $this->entityManager->getRepository(Jeun::class)->findOneBy([
            'createdBy' => $currentUser
        ]);
        if(is_null($jeun)) {
            $jeun = new Jeun();
            $jeun->setCreatedBy($currentUser);
            $this->entityManager->persist($jeun);
            $this->entityManager->flush();
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'jeunText' => $jeun->getText(),
            'jeunNbDays' => $jeun->getNbDays(),
            'jeunNbDaysR' => $jeun->getJeunNbDaysR(),
            'selectedYear' => $jeun->getSelectedYear(),
        ]);
        return $jsonResponse;
    }

    #[Route('/get-jeun-string-for-testatement')]
    public function loadJeunstring(Request $request): Response {
        $currentUser = $this->getUser();
        if(!is_null($request->get('testament'))) {
            $testament = $this->entityManager->getRepository(Testament::class)->findOneBy([
                'id' => $request->get('testament')
            ]);
            $currentUser = $testament->getCreatedBy();
        }
        $jeun = $this->entityManager->getRepository(Jeun::class)->findOneBy([
            'createdBy' => $currentUser
        ]);
        $jeunText = "Aucun jour à rattraper";
        if(!is_null($jeun)) {
            $restNbDays = $jeun->getNbDays() - $jeun->getJeunNbDaysR();
            if($restNbDays == 1) {
                $jeunText = $restNbDays. " jour à rattraper pour ".$jeun->getSelectedYear();
            } elseif($restNbDays > 1) {
                $jeunText = $restNbDays. " jours à rattraper pour ".$jeun->getSelectedYear();
            }
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'jeunText' => $jeunText,
        ]);
        return $jsonResponse;
    }



    #[Route('/save-jeun-textnbdays', methods: ['POST'])]
    public function saveJeun(Request $request): Response
    {
        $currentUser = $this->getUser();
        $jeun = $this->entityManager->getRepository(Jeun::class)->findOneBy([
            'createdBy' => $currentUser
        ]);
        $jeun->setText($request->get('jeunText'));
        $jeun->setNbDays(intval($request->get('jeunNbDays')));
        $jeun->setJeunNbDaysR(intval($request->get('jeunNbDaysR')));
        $jeun->setSelectedYear(intval($request->get('selectedYear')));
        $this->entityManager->persist($jeun);
        $this->entityManager->flush();
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData();
        return $jsonResponse;
    }
}
