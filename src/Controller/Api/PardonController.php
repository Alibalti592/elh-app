<?php

namespace App\Controller\Api;

use App\Entity\Pardon;
use App\Entity\Location;
use App\Entity\PardonShare;
use App\Entity\Pompe;
use App\Entity\Relation;
use App\Entity\Todo;
use App\Entity\User;
use App\Services\CRUDService;
use App\Services\NotificationService;
use App\Services\UtilsService;
use App\UIBuilder\PardonUI;
use App\UIBuilder\TodoUI;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PardonController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly UtilsService $utilsService,
                                private readonly CRUDService $CRUDService, private readonly PardonUI $pardonUI, private readonly NotificationService $notificationService) {}

//    #[Route('/load-pardons')]
//    public function loadPardonList(Request $request): Response
//    {
//        $currentUser = $this->getUser();
//        $pardons = $this->entityManager->getRepository(Pardon::class)->getPardonsOfuser($currentUser);
//        $sharedPardons = $this->entityManager->getRepository(Pardon::class)->getPardonsSharedWith($currentUser);
//        $pardonUIs = [];
//        $sharedPardonUIs = [];
//        foreach ($pardons as $pardon) {
//            $pardonUIs[] = $this->pardonUI->getPardon($pardon, $currentUser);
//        }
//        foreach ($sharedPardons as $pardon) {
//            $sharedPardonUIs[] = $this->pardonUI->getPardon($pardon, $currentUser);
//        }
//        $jsonResponse = new JsonResponse();
//        $jsonResponse->setData([
//            'pardons' => $pardonUIs,
//            'sharedPardons' => $sharedPardonUIs,
//        ]);
//        return $jsonResponse;
//    }

    #[Route('/save-pardon', methods: ['POST'])]
    public function savePardon(Request $request): Response
    {
        $pardonDatas = json_decode($request->get('pardon'), true);
        $currentUser = $this->getUser();
        $pardon = $this->entityManager->getRepository(Pardon::class)->findOneBy([
            'id' => $pardonDatas['id'],
            'createdBy' => $currentUser
        ]);
        if(is_null($pardon)) {
            $pardon = new Pardon();
            $pardon->setCreatedBy($currentUser);
        }
        $pardonDatas['content'] = $this->utilsService->limitText($pardonDatas['content'], 2000);
        $pardon->setFromUI($pardonDatas);
        $this->entityManager->persist($pardon);
        $this->entityManager->persist($currentUser);
        $this->entityManager->flush();

        //Partages avec les contacts et notif
        $relations = $this->entityManager->getRepository(Relation::class)
            ->findListOfRelationUsers($currentUser, ['active'], 250);
        $existingSharesUserIds = $this->entityManager->getRepository(PardonShare::class)->findUserIdsInShare($pardon);
        /** @var Relation $relation */
        foreach ($relations as $relation) {
            if($relation->getUserTarget()->getId() == $currentUser->getId()) {
                $relationUser = $relation->getUserSource();
            } else {
                $relationUser = $relation->getUserTarget();
            }
            if(!in_array($relationUser->getId(), $existingSharesUserIds)) {
                $pardonShare = new PardonShare();
                $pardonShare->setPardon($pardon);
                $pardonShare->setShareWith($relationUser);
                $this->entityManager->persist($pardonShare);
                $this->notificationService->notifForSharePardon($pardon, $relationUser);
            }
        }
        $this->entityManager->flush();
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData();
        return $jsonResponse;
    }
}
