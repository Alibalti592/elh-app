<?php

namespace App\Controller\Api;

use App\Repository\NotifToSendRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;




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
                $newRemaining = max(0, $obligation->getRemainingAmount() - $tranche->getAmount());
                if($newRemaining < 0){
                    return $this->json(['error' => 'Montant de la tranche supérieur au montant restant de l\'obligation'], 400);
                }
                if ($obligation) {
                    $obligation->setRemainingAmount($newRemaining);
                    if($newRemaining == 0){
                        $obligation->setStatus('refund');
                    }
                }
            }
        }
    }else{
         $trancheId = $notifData['trancheId'] ?? null;
         $tranche = $trancheRepo->find($trancheId);
          $tranche->setStatus('refusée');
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
            ->findBy(['user' => $currentUser, 'type' => 'tranche', 'status' => 'pending'], ['sendAt' => 'DESC']);

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

      #[Route('/notifs/ack', name: 'notif_ack_bulk', methods: ['POST'])]
    public function ackBulk(
        Request $request,
        NotifToSendRepository $notifRepo,
        EntityManagerInterface $em
    ): JsonResponse {
        $currentUser = $this->getUser();
        if (!$currentUser) {
            return new JsonResponse(['error' => 'Utilisateur non authentifié'], 401);
        }

        $payload = json_decode($request->getContent(), true) ?? [];
        $ids = $payload['ids'] ?? [];

        // sanitize: ints only, unique
        $ids = array_values(array_unique(array_map('intval', array_filter($ids, fn($v) => is_numeric($v)))));

        if (empty($ids)) {
            return $this->json(['updated' => 0, 'ids' => []]);
        }

        // fetch only current user's notifs
        $qb = $notifRepo->createQueryBuilder('n');
        $notifs = $qb->where($qb->expr()->in('n.id', ':ids'))
           
            ->setParameter('ids', $ids)
            
            ->getQuery()
            ->getResult();

        foreach ($notifs as $n) {
            // Set the notification itself to "validée"
            $n->setStatus('validée');
        }

        $em->flush();

        return $this->json([
            'updated' => count($notifs),
            'ids' => array_map(fn($n) => $n->getId(), $notifs),
        ]);
    }
    
}
