<?php

namespace App\Controller\Api;

use App\Entity\Location;
use App\Entity\Maraude;
use App\Services\CRUDService;
use App\Services\UtilsService;
use App\UIBuilder\MaraudeUI;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MaraudeController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly UtilsService $utilsService,
                                private readonly CRUDService $CRUDService, private readonly MaraudeUI $maraudeUI) {}

    #[Route('/load-maraudes')]
    public function loadList(Request $request): Response
    {
        $currentUser = $this->getUser();
        $myMaraudes = $request->get('mymaraudes') === 'true';
        if(!$myMaraudes) {
            $location = json_decode($request->get('location'), true);
            $defaultDistance = intval($request->get('distance')); //en km
            $maraudes = $this->entityManager->getRepository(Maraude::class)
                ->findMaraudesByDistance($location['lat'], $location['lng'], $defaultDistance);
        } else {
            $maraudes = $this->entityManager->getRepository(Maraude::class)
                ->findMyMaraudes($currentUser);
        }
        $maraudeUIs = [];
        foreach ($maraudes as $maraude) {
            if($myMaraudes) {
                $maraudeUIs[] = $this->maraudeUI->getMaraude($maraude, $currentUser);
            } else {
                $maraudeUIs[] = $this->maraudeUI->getMaraude($maraude['maraude'], $currentUser,  $maraude['distance']);
            }

        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'maraudes' => $maraudeUIs,
        ]);
        return $jsonResponse;
    }


    #[Route('/save-maraude', methods: ['POST'])]
    public function saveMaraude(Request $request): Response
    {
        $maraudeDatas = json_decode($request->get('maraude'), true);
        $currentUser = $this->getUser();
        $maraude = null;
        if(!is_null($maraudeDatas['id'])) {
            $maraude = $this->entityManager->getRepository(Maraude::class)->findOneBy([
                'id' => $maraudeDatas['id'],
                'managedBy' => $currentUser
            ]);
        }
        if(is_null($maraude)) {
            $maraude = new Maraude();
            $maraude->setManagedBy($currentUser);
            $maraude->setValidated(false);
        }
        //location
        $location = $maraude->getLocation();
        if(is_null($location)) {
            $location = new Location();
            $maraude->setLocation($location);
        }
        $maraudeDatas['description'] = strlen($maraudeDatas['description']) > 300 ? mb_substr($maraudeDatas['description'], 0, 300) : $maraudeDatas['description'];
        $maraudeDatas['description'] = $this->utilsService->htmlEncodeBeforeSave($maraudeDatas['description']);
        $maraude->setFromUI($maraudeDatas);
        $locationDatas = $maraudeDatas['location'];
        $location->setFromUI($locationDatas);
        $this->entityManager->persist($maraude);
        $this->entityManager->persist($currentUser);
        $this->entityManager->flush();
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData();
        return $jsonResponse;
    }
}
