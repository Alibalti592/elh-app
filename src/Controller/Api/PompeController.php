<?php

namespace App\Controller\Api;

use App\Entity\ChatMessage;
use App\Entity\Location;
use App\Entity\Pompe;
use App\Entity\PompeNotification;
use App\Services\Chat\ChatNotificationService;
use App\Services\Chat\ThreadService;
use App\Services\CRUDService;
use App\Services\NotificationService;
use App\Services\UtilsService;
use App\UIBuilder\Chat\ThreadUI;
use App\UIBuilder\PompeUI;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PompeController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly UtilsService $utilsService,
                                private readonly CRUDService $CRUDService, private readonly PompeUI $pompeUI, private readonly ThreadService $threadService,
                                private readonly ThreadUI $threadUI, private readonly ChatNotificationService $chatNotificationService,
                                private readonly NotificationService $notificationService) {}

    #[Route('/load-pompes')]
    public function loadList(Request $request): Response
    {
        $currentUser = $this->getUser();
        $location = json_decode($request->get('location'), true);
        $defaultDistance = intval($request->get('distance')); //en km
        $pompes = $this->entityManager->getRepository(Pompe::class)
            ->findPompesByDistance($location['lat'], $location['lng'], $defaultDistance);
        $pompeUIs = [];
        foreach ($pompes as $pompe) {
            $pompeUIs[] = $this->pompeUI->getPompe($pompe['pompe'], $pompe['distance']);
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'pompes' => $pompeUIs,
        ]);
        return $jsonResponse;
    }

    #[Route('/is-pf')]
    public function isPF(): Response
    {
        $currentUser = $this->getUser();
        $nbPompes = $this->entityManager->getRepository(Pompe::class)->countManagedPompes($currentUser);
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'isPompeOwner' => $nbPompes > 0
        ]);
        return $jsonResponse;
    }

    #[Route('/load-my-pompes')]
    public function loadMyPompes(): Response
    {
        $currentUser = $this->getUser();
        $pompes = $this->entityManager->getRepository(Pompe::class)->findManagedPompe($currentUser);
        $isPompeOwner = !empty($pompes);
        $pompeUIs = [];
        foreach ($pompes as $pompe) {
            $pompeUIs[] = $this->pompeUI->getPompe($pompe);
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'pompes' => $pompeUIs,
            'isPompeOwner' => $isPompeOwner
        ]);
        return $jsonResponse;
    }

    #[Route('/save-pompe', methods: ['POST'])]
    public function savePompe(Request $request): Response
    {
        $pompeDatas = json_decode($request->get('pompe'), true);
        $currentUser = $this->getUser();
        $pompe = null;
        if(!is_null($pompeDatas['id'])) {
            $pompe = $this->entityManager->getRepository(Pompe::class)->findOneBy([
                'id' =>  $pompeDatas['id'],
                'managedBy' => $currentUser
            ]);
            if(is_null($pompe)) {
                throw new \ErrorException('cnat managed pompe');
            }
        }
        $notifAdmin = false;
        if(is_null($pompe)) {
            $pompe = new Pompe();
            $pompe->setManagedBy($currentUser);
            $pompe->setValidated(false);
            $pompe->setOnline(false);
            $notifAdmin = true;
        }
        //location
        $location = $pompe->getLocation();
        if(is_null($location)) {
            $location = new Location();
            $pompe->setLocation($location);
        }
//        $description = $this->utilsService->htmlEncodeBeforeSave($pompeDatas['description']);
        $pompe->setName($pompeDatas['name']);

        $pompe->setPhone($pompeDatas['phone']);
        $pompe->setPhonePrefix($pompeDatas['phonePrefix']);

        $pompe->setPhoneUrgence($pompeDatas['phoneUrgence']);
        $pompe->setPrefixUrgence($pompeDatas['phoneUrgencePrefix']);

        $pompe->setEmailpro($pompeDatas['emailPro']);
        $pompe->setFullname($pompeDatas['namePro']);

//        $pompe->setDescription($description);
        $locationDatas = $pompeDatas['location'];
        $location->setFromUI($locationDatas);
        $this->entityManager->persist($pompe);
        $this->entityManager->persist($currentUser);
        $this->entityManager->flush();
        if($notifAdmin) {
            $this->notificationService->notifNewPFAdmin($pompe);
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData();
        return $jsonResponse;
    }

    #[Route('/load-pompe-demands')]
    public function loadDemands(Request $request): Response
    {
        $currentUser = $this->getUser();
        $pompes = $this->entityManager->getRepository(Pompe::class)->findManagedPompe($currentUser);
        $pompeIds = [];
        foreach ($pompes as $pompe) {
            $pompeIds[] = $pompe->getId();
        }
        $pompeNotifs = $this->entityManager->getRepository(PompeNotification::class)
            ->findDemandForPompes($pompeIds);
        $pompeNotifUIs = [];
        foreach ($pompeNotifs as $pompeNotif) {
            $pompeNotifUIs[] = $this->pompeUI->getPompeDemand($pompeNotif);
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'demands' => $pompeNotifUIs,
        ]);
        return $jsonResponse;
    }


    #[Route('/pompe-accept-demand', methods: ['POST'])]
    public function pompeAcceptDemand(Request $request): Response
    {
        $pompeDemandDatas = json_decode($request->get('pompe'), true);
        $currentUser = $this->getUser();
        /** @var PompeNotification $pompeDemand */
        $pompeNotif = $this->entityManager->getRepository(PompeNotification::class)->findOneBy([
            'id' => $pompeDemandDatas['id'],
        ]);
        $pompe = $pompeNotif->getPompe();
        $jsonResponse = new JsonResponse();
        if ($pompe->getManagedBy()->getId() != $currentUser->getId()) {
            throw new \ErrorException('Cant managed pompe');
        } elseif($pompeNotif->getStatus() != 'canDemand') {
            $jsonResponse = new JsonResponse();
            $jsonResponse->setStatusCode(500);
            $jsonResponse->setData([
                'message' => 'Vous ne pouvez plus rentrez en contact avec cette personne'
            ]);
            return $jsonResponse;
        }
        //create thread
        $user2 = $pompeNotif->getDece()->getCreatedBy();
        $thread = $this->threadService->getSimpleThreadFoUsers($currentUser, $user2);
        //add first message avec nom thread
        if(is_null($thread->getLastMessage())) {
            $message = new ChatMessage();
            $message->setCreatedBy($currentUser);
            $message->setContent("La pompe funèbre : ".$pompe->getName()." souhaite vous accompagner");
            $message->setChatThread($thread);
            $thread->setLastMessage($message);
            $thread->setLastUpdate(new \DateTime('now'));
            $this->entityManager->persist($message);
            $this->entityManager->persist($thread);
            $this->entityManager->flush();
            $this->chatNotificationService->addNotificationsForMessage($message, $thread);
        }
        $threadUI = $this->threadUI->getThreadUI($thread, $currentUser->getId());
        //change status
        $pompeNotif->setStatus('accepted');
        $pompeNotif->setAccepted(true);
        $this->entityManager->persist($pompeNotif);
        $this->entityManager->flush();
        //check if need to change status of other notifs
        $dece = $pompeNotif->getDece();
        $nbMaxAccept = 3;
        $nbAccpeted = $this->entityManager->getRepository(PompeNotification::class)->countForDece($dece, "accepted");
        if($nbAccpeted >= $nbMaxAccept) {
            $this->entityManager->getRepository(PompeNotification::class)->updateStatusForDece($dece, "canDemand", "rejected") ;
        }
        $jsonResponse->setStatusCode(200);
        $jsonResponse->setData([
            'thread' => $threadUI
        ]);
        return $jsonResponse;
    }

    #[Route('/pompe-demand-load-chat', methods: ['POST'])]
    public function pompetDemandLoadChat(Request $request): Response
    {
        $pompeDemandDatas = json_decode($request->get('pompe'), true);
        $currentUser = $this->getUser();
        /** @var PompeNotification $pompeDemand */
        $pompeNotif = $this->entityManager->getRepository(PompeNotification::class)->findOneBy([
            'id' => $pompeDemandDatas['id'],
        ]);
        $pompe = $pompeNotif->getPompe();
        $jsonResponse = new JsonResponse();
        if ($pompe->getManagedBy()->getId() != $currentUser->getId() || $pompeNotif->getStatus() != 'accepted') {
            throw new \ErrorException('Cant managed pompe');
        }
        //get chat thread
        $user2 = $pompeNotif->getDece()->getCreatedBy();
        $thread = $this->threadService->getSimpleThreadFoUsers($currentUser, $user2);
        $threadUI = $this->threadUI->getThreadUI($thread, $currentUser->getId());
        $jsonResponse->setData([
            'thread' => $threadUI
        ]);
        return $jsonResponse;
    }
}
