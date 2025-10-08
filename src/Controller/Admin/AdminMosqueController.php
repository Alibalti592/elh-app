<?php

namespace App\Controller\Admin;

use App\Entity\Location;
use App\Entity\Mosque;
use App\Entity\User;
use App\Services\CRUDService;
use App\Services\UtilsService;
use App\UIBuilder\MosqueUI;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminMosqueController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly UtilsService $utilsService,
                                private readonly CRUDService $CRUDService, private readonly MosqueUI $mosqueUI) {}

    #[Route('/admin/mosque', name: 'admin_mosque_list')]
    public function index(): Response
    {
        return $this->render('admin/modules/mosque/list.twig', [

        ]);
    }

    #[Route('/v-load-list-mosques')]
    public function loadList(Request $request): Response
    {
        $crudParams = $this->CRUDService->getListParametersFromRequest($request);
        $mosques = $this->entityManager->getRepository(Mosque::class)->findListFilteredAdmin($crudParams);
        $count = $this->entityManager->getRepository(Mosque::class)->countListFiltered($crudParams);
        $mosqueUIs = [];
        foreach ($mosques as $mosque) {
            $mosqueUIs[] = $this->mosqueUI->getMosque($mosque);
        }
        $newMosque = new Mosque();
        $newMosque->setLocation(new Location());
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'mosques' => $mosqueUIs,
            'mosqueIni' => $this->mosqueUI->getMosque($newMosque),
            'totalItems' => $count,
        ]);
        return $jsonResponse;
    }

    #[Route('/v-save-mosque', methods: ['POST'])]
    public function savemosque(Request $request): Response
    {
        //"id":null,"name":"Mosquee Lyton","online":true,"description":"<p>fdsq ff sq</p>","location":{"label":"Lyon","postcode":"69001","city":"Lyon","region":"69, Rhône, Auvergne-Rhône-Alpes","lat":45.758,"lng":4.835}
        $mosqueDatas = json_decode($request->get('mosque'), true);
        if(!is_null($mosqueDatas['id'])) {
            $mosque = $this->entityManager->getRepository(Mosque::class)->findOneBy([
                'id' =>  $mosqueDatas['id']
            ]);
            if(is_null($mosque)) {
                throw new \ErrorException("Mosque introuvable");
            }
        } else {
            $mosque = new Mosque();
        }
        $description = $this->utilsService->htmlEncodeBeforeSave($mosqueDatas['description']);
        $mosque->setName($mosqueDatas['name']);
        $mosque->setDescription($description);
        $online = $mosqueDatas['online'] === true;
        $mosque->setOnline($online);
        //location
        $location = $mosque->getLocation();
        if(is_null($location)) {
            $location = new Location();
            $mosque->setLocation($location);
        }
        $locationDatas = $mosqueDatas['location'];
        $location->setFromUI($locationDatas);

        if(isset($mosqueDatas['managedBy'])) {
            $managedBy = $this->entityManager->getRepository(User::class)->findOneBy([
                'id' => $mosqueDatas['managedBy']['id']
            ]);
            $mosque->setManagedBy($managedBy);
        }

        $this->entityManager->persist($mosque);
        $this->entityManager->flush();
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData();
        return $jsonResponse;
    }
}
