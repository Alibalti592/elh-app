<?php

namespace App\Controller\Admin;

use App\Entity\Location;
use App\Entity\Imam;
use App\Services\CRUDService;
use App\Services\UtilsService;
use App\UIBuilder\ImamUI;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminImamController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly UtilsService $utilsService,
                                private readonly CRUDService $CRUDService, private readonly ImamUI $imamUI) {}

    #[Route('/admin/imam', name: 'admin_imam_list')]
    public function index(): Response
    {
        return $this->render('admin/modules/default.twig', [
            'title' => 'Imams',
            'vueID' => 'admin-imam-list'
        ]);
    }

    #[Route('/v-load-list-imams')]
    public function loadList(Request $request): Response
    {
        $crudParams = $this->CRUDService->getListParametersFromRequest($request);
        $imams = $this->entityManager->getRepository(Imam::class)->findListFiltered($crudParams);
        $count = $this->entityManager->getRepository(Imam::class)->countListFiltered($crudParams);
        $imamUIs = [];
        foreach ($imams as $imam) {
            $imamUIs[] = $this->imamUI->getImam($imam);
        }
        $newImam = new Imam();
        $newImam->setLocation(new Location());
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'imams' => $imamUIs,
            'imamIni' => $this->imamUI->getImam($newImam),
            'totalItems' => $count,
        ]);
        return $jsonResponse;
    }

    #[Route('/v-save-imam', methods: ['POST'])]
    public function saveimam(Request $request): Response
    {
        //"id":null,"name":"Imame Lyton","online":true,"description":"<p>fdsq ff sq</p>","location":{"label":"Lyon","postcode":"69001","city":"Lyon","region":"69, Rhône, Auvergne-Rhône-Alpes","lat":45.758,"lng":4.835}
        $imamDatas = json_decode($request->get('imam'), true);
        if(!is_null($imamDatas['id'])) {
            $imam = $this->entityManager->getRepository(Imam::class)->findOneBy([
                'id' =>  $imamDatas['id']
            ]);
            if(is_null($imam)) {
                throw new \ErrorException("Imam introuvable");
            }
        } else {
            $imam = new Imam();
        }
        $description = $this->utilsService->htmlEncodeBeforeSave($imamDatas['description']);
        $imam->setName($imamDatas['name']);
        $imam->setDescription($description);
        $online = $imamDatas['online'] === true;
        $imam->setOnline($online);
        //location
        $location = $imam->getLocation();
        if(is_null($location)) {
            $location = new Location();
            $imam->setLocation($location);
        }
        $locationDatas = $imamDatas['location'];
        $location->setLabel($locationDatas['label']);
        $location->setAdress($locationDatas['adress']);
        $location->setRegion($locationDatas['region']);
        $location->setCity($locationDatas['city']);
        $location->setPostCode($locationDatas['postcode']);
        $location->setLat($locationDatas['lat']);
        $location->setLng($locationDatas['lng']);
        $this->entityManager->persist($imam);
        $this->entityManager->flush();
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData();
        return $jsonResponse;
    }
}
