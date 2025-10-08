<?php

namespace App\Controller\Api;

use App\Entity\Location;
use App\Entity\Mosque;
use App\Entity\MosqueFavorite;
use App\Entity\MosqueNotifDece;
use App\Entity\Pompe;
use App\Entity\PompeNotification;
use App\Entity\Salat;
use App\Services\CRUDService;
use App\Services\UtilsService;
use App\UIBuilder\MosqueUI;
use App\UIBuilder\SalatUI;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MosqueController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly UtilsService $utilsService,
                                private readonly CRUDService            $CRUDService, private readonly MosqueUI $mosqueUI, private readonly SalatUI $salatUI) {}


    #[Route('/load-my-mosques')]
    public function loadMyMosques(Request $request): Response
    {
        $currentUser = $this->getUser();
        $mosqueFavs = $this->entityManager->getRepository(MosqueFavorite::class)->findMosqueFavorited($currentUser);
        $myownMosques = $this->entityManager->getRepository(Mosque::class)->findMyMosquesGestion($currentUser);
        $mosqueUIs = [];
        foreach ($mosqueFavs as $mosqueFav) {
            $mosque = $mosqueFav->getMosque();
            $mosqueUI = $this->mosqueUI->getMosque($mosque);
            $mosqueUI['isFavorite'] = true;
            $mosqueUIs[] = $mosqueUI;
        }
        $mosqueownUIs = [];
        $isOwner = !empty($myownMosques);
        foreach ($myownMosques as $mosque) {
            $mosqueUI = $this->mosqueUI->getMosque($mosque);
            $mosqueUI['isFavorite'] = true;
            $mosqueownUIs[] = $mosqueUI;
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'mosques' => $mosqueUIs,
            'ownMosques' => $mosqueownUIs,
            'isOwner' => $isOwner,
        ]);
        return $jsonResponse;
    }

    #[Route('/load-mosques')]
    public function loadList(Request $request): Response
    {
        $currentUser = $this->getUser();
        $location = json_decode($request->get('location'), true);
        $defaultDistance = intval($request->get('distance')); //en km, 10 désormais
        $mosques = $this->entityManager->getRepository(Mosque::class)
            ->findMosquesByDistance($location['lat'], $location['lng'], $defaultDistance);
        if(empty($mosques)) {
            $mosques = $this->entityManager->getRepository(Mosque::class)
                ->findMosquesByDistance($location['lat'], $location['lng'], 50);
        }
        $favoriteIds = $this->entityManager->getRepository(MosqueFavorite::class)->findMosqueFavoriteIds($currentUser);
        $mosqueUIs = [];
        foreach ($mosques as $mosque) {
            $mosqueUIs[] = $this->mosqueUI->getMosque($mosque['mosque'], $mosque['distance'], $favoriteIds);
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'mosques' => $mosqueUIs,
        ]);
        return $jsonResponse;
    }

    #[Route('/mark-favorite-mosque', methods: ['POST'])]
    public function saveFavorite(Request $request): Response
    {
        $currentUser = $this->getUser();
        $mosque = $this->entityManager->getRepository(Mosque::class)->findOneBy([
            'id' => $request->get('mosque')
        ]);
        $jsonResponse = new JsonResponse();
        if(is_null($mosque)) {
            $jsonResponse->setStatusCode(404);
            return $jsonResponse;
        }
        //markFavorites
        $existFavorite = $this->entityManager->getRepository(MosqueFavorite::class)->findMosqueIsFavorite($currentUser, $mosque);
        if(!is_null($existFavorite)) {
            $this->entityManager->remove($existFavorite);
            $this->entityManager->flush();
        } else {
            $existFavorite = new MosqueFavorite();
            $existFavorite->setMosque($mosque);
            $existFavorite->setUser($currentUser);
            $this->entityManager->persist($existFavorite);
            $this->entityManager->flush();
        }

        return $jsonResponse;
    }

    #[Route('/save-mosque', methods: ['POST'])]
    public function saveMosque(Request $request): Response
    {
        $mosqueDatas = json_decode($request->get('mosque'), true);
        $currentUser = $this->getUser();
        $mosque = $this->entityManager->getRepository(Mosque::class)->findOneBy([
            'id' =>  $mosqueDatas['id'],
            'managedBy' => $currentUser
        ]);
        if(is_null($mosque)) {
            throw new \ErrorException('cnat managed $mosque');
        }
        $description = $this->utilsService->htmlEncodeBeforeSave($mosqueDatas['description']);
        $mosque->setDescription($description);
        $this->entityManager->persist($mosque);
        $this->entityManager->flush();
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData();
        return $jsonResponse;
    }

    #[Route('/load-mosque-deces')]
    public function loadDemands(Request $request): Response
    {
        $currentUser = $this->getUser();
        $mosque = $this->entityManager->getRepository(Mosque::class)->findOneBy([
            'id' =>  $request->get('mosqueId'),
        ]);
        if(is_null($mosque)) {
            throw new \ErrorException(' $mosque no found');
        }
        $salats = $this->entityManager->getRepository(Salat::class)
            ->getSalatsInMosques([$mosque->getId()], []);
        $salatsUIs = [];
        foreach ($salats as $salat) {
            $salatsUIs[] = $this->salatUI->getSalat($salat);
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'salats' => $salatsUIs,
        ]);
        return $jsonResponse;
    }

}
