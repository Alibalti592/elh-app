<?php

namespace App\Controller\Api;

use App\Repository\NotifToSendRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;



class NotifController extends AbstractController
{
    #[Route('/notif/{id}/respond', name: 'notif_respond', methods: ['POST'])]
public function respondNotif(
    Request $request,
    NotifToSendRepository $notifRepo,
    EntityManagerInterface $em,
    \App\Repository\TrancheRepository $trancheRepo, // inject the tranche repo
    int $id
): JsonResponse {
    $notif = $notifRepo->find($id);

    if (!$notif) {
        return $this->json(['error' => 'Notification not found'], 404);
    }

    $data = json_decode($request->getContent(), true);
    $action = $data['action'] ?? null;

    if (!in_array($action, ['accept', 'decline'])) {
        return $this->json(['error' => 'Invalid action'], 400);
    }

    $notif->setStatus($action);

    if ($action === 'accept') {
        // decode datas to get trancheId
        $notifData = json_decode($notif->getDatas(), true);
        $trancheId = $notifData['trancheId'] ?? null;

        if ($trancheId) {
            $tranche = $trancheRepo->find($trancheId);
            if ($tranche) {
                $tranche->setStatus('validée');

                $obligation = $tranche->getObligation();
                if ($obligation) {
                    $newRemaining = max(0, $obligation->getRemainingAmount() - $tranche->getAmount());
                    $obligation->setRemainingAmount($newRemaining);
                }
            }
        }
    }

    $em->flush();

    return $this->json([
        'success' => true,
        'status' => $notif->getStatus(),
    ]);
}


    // New route to fetch all notifications
    #[Route('/notifs', name: 'fetch_notifications', methods: ['GET'])]
     public function fetchNotifications(Request $request,NotifToSendRepository $notifRepo): Response
   
    {
        $currentUser = $this->getUser();
         if (!$currentUser) {
            return new JsonResponse(['error' => 'Utilisateur non authentifié'], 401);
        }
        $notifications = $notifRepo
            ->findBy(['user' => $currentUser], ['sendAt' => 'DESC']);

        $data = array_map(function($notif) {
            return [
                'id' => $notif->getId(),
                'title' => $notif->getTitle(),
                'message' => $notif->getMessage(),
                'type' => $notif->getType(),
                'view' => $notif->getView(),
                'sendAt' => $notif->getSendAt()->format('Y-m-d H:i:s'),
                'status' => $notif->getStatus(),
            ];
        }, $notifications);

        // return $this->json($data);
          return new JsonResponse($data);
    }
    
}
