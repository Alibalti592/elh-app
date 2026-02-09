<?php

namespace App\Controller\Api;

use App\Entity\Jeun;
use App\Entity\Testament;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class JeunController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager) {}

    #[Route('/get-jeun', name: 'jeun_get_legacy', methods: ['GET'])]
    #[Route('/jeun', name: 'jeun_get', methods: ['GET'])]
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
            'years' => $jeun->getYears(),
            'totalRemainingDays' => $jeun->getTotalRemainingDays(),
        ]);
        return $jsonResponse;
    }

    #[Route('/get-jeun-string-for-testatement', name: 'jeun_summary_legacy', methods: ['GET'])]
    #[Route('/jeun/summary', name: 'jeun_summary', methods: ['GET'])]
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
            $jeunText = $jeun->getRemainingDaysSummary();
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'jeunText' => $jeunText,
        ]);
        return $jsonResponse;
    }



    #[Route('/save-jeun-textnbdays', name: 'jeun_save_legacy', methods: ['POST'])]
    #[Route('/save-jeun', name: 'jeun_save', methods: ['POST'])]
    public function saveJeun(Request $request): Response
    {
        $currentUser = $this->getUser();
        $jeun = $this->entityManager->getRepository(Jeun::class)->findOneBy([
            'createdBy' => $currentUser
        ]);
        if(is_null($jeun)) {
            $jeun = new Jeun();
            $jeun->setCreatedBy($currentUser);
        }

        $payload = [];
        $raw = $request->getContent();
        if(is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);
            if(is_array($decoded)) {
                $payload = $decoded;
            }
        }

        $jeunText = $payload['jeunText'] ?? $request->get('jeunText');
        $jeun->setText($jeunText);

        $yearsPayload = $payload['years'] ?? $request->get('years');
        $years = null;
        if(!is_null($yearsPayload) && $yearsPayload !== "") {
            if(is_string($yearsPayload)) {
                $decoded = json_decode($yearsPayload, true);
                if(is_array($decoded)) {
                    $years = $decoded;
                }
            } elseif(is_array($yearsPayload)) {
                $years = $yearsPayload;
            }
        }

        $selectedYear = intval($payload['selectedYear'] ?? $request->get('selectedYear'));
        $nbDays = intval($payload['jeunNbDays'] ?? $payload['nbDays'] ?? $request->get('jeunNbDays'));
        $nbDaysR = intval($payload['jeunNbDaysR'] ?? $payload['nbDaysR'] ?? $request->get('jeunNbDaysR'));

        if(!is_null($years)) {
            $jeun->setYears($years);
            if($selectedYear > 0) {
                $jeun->setSelectedYear($selectedYear);
            }
            $jeun->syncLegacyFieldsFromYears($jeun->getSelectedYear());
        } else {
            if($selectedYear > 0) {
                $jeun->setSelectedYear($selectedYear);
            }
            $jeun->setNbDays($nbDays);
            $jeun->setJeunNbDaysR($nbDaysR);
            $yearToMerge = $jeun->getSelectedYear() ?? $selectedYear;
            if(!is_null($yearToMerge) && $yearToMerge > 0) {
                $jeun->mergeYearEntry($yearToMerge, $nbDays, $nbDaysR);
            }
            $jeun->syncLegacyFieldsFromYears($jeun->getSelectedYear());
        }
        $this->entityManager->persist($jeun);
        $this->entityManager->flush();
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData();
        return $jsonResponse;
    }
}
