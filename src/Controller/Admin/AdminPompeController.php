<?php

namespace App\Controller\Admin;

use App\Entity\Location;
use App\Entity\Pompe;
use App\Entity\User;
use App\Services\CRUDService;
use App\Services\NotificationService;
use App\Services\UtilsService;
use App\UIBuilder\PompeUI;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminPompeController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly UtilsService $utilsService,
                                private readonly CRUDService $CRUDService, private readonly PompeUI $pompeUI, private readonly NotificationService
     $notificationService) {}

    #[Route('/admin/pompe', name: 'admin_pompe_list')]
    public function index(): Response
    {
        return $this->render('admin/modules/pompe/list.twig', [

        ]);
    }

    #[Route('/v-load-list-pompes')]
    public function loadList(Request $request): Response
    {
        $crudParams = $this->CRUDService->getListParametersFromRequest($request);
        $pompes = $this->entityManager->getRepository(Pompe::class)->findListFiltered($crudParams);
        $count = $this->entityManager->getRepository(Pompe::class)->countListFiltered($crudParams);
        $pompeUIs = [];
        foreach ($pompes as $pompe) {
            $pompeUIs[] = $this->pompeUI->getPompe($pompe);
        }
        $pompeIni = new Pompe();
        $pompeIni->setLocation(new Location());
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'pompes' => $pompeUIs,
            'pompeIni' => $this->pompeUI->getPompe($pompeIni),
            'totalItems' => $count,
        ]);
        return $jsonResponse;
    }

    #[Route('/v-save-pompe', methods: ['POST'])]
    public function savePompe(Request $request): Response
    {
        $pompeDatas = json_decode($request->get('pompe'), true);
        if(!is_null($pompeDatas['id'])) {
            $pompe = $this->entityManager->getRepository(Pompe::class)->findOneBy([
                'id' =>  $pompeDatas['id']
            ]);
            $sendNotifValidation = !$pompe->isOnline();
            if(is_null($pompe)) {
                throw new \ErrorException("Pompe introuvable");
            }
        } else {
            $sendNotifValidation = false;
            $pompe = new Pompe();
        }
        $description = $this->utilsService->htmlEncodeBeforeSave($pompeDatas['description']);
        if($sendNotifValidation && $pompeDatas['online'] === true) {
            $sendNotifValidation = true;
        }
        $online = $pompeDatas['online'] === true;
        $pompe->setName($pompeDatas['name']);
        $pompe->setDescription($description);
        $pompe->setOnline($online);
        $pompe->setValidated(true);
        //location
        $location = $pompe->getLocation();
        if(is_null($location)) {
            $location = new Location();
            $pompe->setLocation($location);
        }
        if(isset($pompeDatas['user'])) {
            $managedBy = $this->entityManager->getRepository(User::class)->findOneBy([
                'id' => $pompeDatas['user']['id']
            ]);
            $pompe->setManagedBy($managedBy);
        }
        $locationDatas = $pompeDatas['location'];
        $location->setLabel($locationDatas['label']);
        $location->setAdress($locationDatas['adress']);
        $location->setRegion($locationDatas['region']);
        $location->setCity($locationDatas['city']);
        $location->setPostCode($locationDatas['postcode']);
        $location->setLat($locationDatas['lat']);
        $location->setLng($locationDatas['lng']);

        if($sendNotifValidation) {
            $this->notificationService->notifNewPFValidationCompte($pompe);
        }

        $this->entityManager->persist($pompe);
        $this->entityManager->flush();
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData();
        return $jsonResponse;
    }
}
