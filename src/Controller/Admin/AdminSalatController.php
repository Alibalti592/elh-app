<?php

namespace App\Controller\Admin;

use App\Entity\Location;
use App\Entity\Salat;
use App\Services\CRUDService;
use App\Services\UtilsService;
use App\UIBuilder\SalatUI;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminSalatController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly UtilsService $utilsService,
                                private readonly CRUDService $CRUDService, private readonly SalatUI $salatUI) {}

    #[Route('/admin/salat', name: 'admin_salat_list')]
    public function index(): Response
    {
        return $this->render('admin/modules/salat/list.twig', [

        ]);
    }

    #[Route('/v-load-list-salats')]
    public function loadList(Request $request): Response
    {
        $currentUser = $this->getUser();
        $crudParams = $this->CRUDService->getListParametersFromRequest($request);
        $salats = $this->entityManager->getRepository(Salat::class)->findListFiltered($crudParams);
        $count = $this->entityManager->getRepository(Salat::class)->countListFiltered($crudParams);
        $salatUIs = [];
        foreach ($salats as $salat) {
            $salatUIs[] = $this->salatUI->getSalatForAdmin($salat);
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'salats' => $salatUIs,
            'totalItems' => $count,
        ]);
        return $jsonResponse;
    }

    #[Route('/v-admin-delete-salat', methods: ['POST'])]
    public function deletesalat(Request $request): Response
    {
        $salatDatas = json_decode($request->get('salat'), true);
        $salat = $this->entityManager->getRepository(Salat::class)->findOneBy([
            'id' =>  $salatDatas['id']
        ]);
        if(is_null($salat)) {
            throw new \ErrorException("Salat introuvable");
        }
        $this->entityManager->remove($salat);
        $this->entityManager->flush();
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([]);
        return $jsonResponse;
    }


    #[Route('/v-save-salat', methods: ['POST'])]
    public function savesalat(Request $request): Response
    {
        $salatDatas = json_decode($request->get('salat'), true);
        if(!is_null($salatDatas['id'])) {
            $salat = $this->entityManager->getRepository(Salat::class)->findOneBy([
                'id' =>  $salatDatas['id']
            ]);
            if(is_null($salat)) {
                throw new \ErrorException("Salat introuvable");
            }
        } else {
            $salat = new Salat();
        }
        $salatDatas['description'] = strlen($salatDatas['description']) > 300 ? mb_substr($salatDatas['description'], 0, 300) : $salatDatas['description'];
        $salatDatas['description'] = $this->utilsService->htmlEncodeBeforeSave($salatDatas['description']);
        $salat->setFromUI($salatDatas);
        $online = $salatDatas['online'] === true;
        $salat->setOnline($online);
        $salat->setValidated($online);
        //location
        $location = $salat->getLocation();
        if(is_null($location)) {
            $location = new Location();
            $salat->setLocation($location);
        }
        $locationDatas = $salatDatas['location'];
        $location->setFromUI($locationDatas);
        $this->entityManager->persist($salat);
        $this->entityManager->flush();
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData();
        return $jsonResponse;
    }
}
