<?php

namespace App\Controller\Api;

use App\Repository\NotifToSendRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\NotifToSend;
use App\Services\FcmNotificationService;



class NotifController extends AbstractController
{
     public function __construct(private FcmNotificationService $fcmNotificationService)
    {}
    #[Route('/notif/{id}/respond', name: 'notif_respond', methods: ['POST'])]
public function respondNotif(
    Request $request,
    NotifToSendRepository $notifRepo,
    EntityManagerInterface $em,
    \App\Repository\TrancheRepository $trancheRepo,
    int $id
): JsonResponse {
    $notif = $notifRepo->find($id);
    $currentUser = $this->getUser();

    if (!$currentUser) {
        return $this->json(['error' => 'Utilisateur non authentifié'], 401);
    }

    if (!$notif) {
        return $this->json(['error' => 'Notification not found'], 404);
    }

    $data = json_decode($request->getContent(), true) ?? [];
    $action = $data['action'] ?? null;

    if (!in_array($action, ['accept', 'decline'], true)) {
        return $this->json(['error' => 'Invalid action'], 400);
    }

    // Always decode notif datas once
    $notifData = json_decode($notif->getDatas() ?? '[]', true) ?: [];
    $trancheId = $notifData['trancheId'] ?? null;

    $tranche = $trancheId ? $trancheRepo->find((int)$trancheId) : null;
    if (!$tranche) {
        return $this->json(['error' => 'Tranche introuvable'], 404);
    }

    $obligation = $tranche->getObligation();

    $notif->setStatus($action);

    $newRemaining = null;

    if ($action === 'accept') {
        $tranche->setStatus('validée');

        if (!$obligation) {
            return $this->json(['error' => 'Obligation introuvable pour la tranche'], 400);
        }

        if ($tranche->getAmount() > $obligation->getRemainingAmount()) {
            // rollback this accept → decline
            $notif->setStatus('decline');
            $tranche->setStatus('refusée');
            $em->flush();
            return $this->json([
                'error' => 'Montant de la tranche supérieur au montant restant de l\'obligation'
            ], 400);
        }

        $newRemaining = max(0, $obligation->getRemainingAmount() - $tranche->getAmount());
        $obligation->setRemainingAmount($newRemaining);
        if ($newRemaining === 0) {
            $obligation->setStatus('refund');
        }
    } else {
        // decline
        $tranche->setStatus('refusée');
    }

    // Build & persist a new notification to the counter-party
    $newnotif = new NotifToSend();
    $newnotif->setSendAt(new \DateTime());
    $newnotif->setType('tranche');
    $newnotif->setView('tranche');
    $newnotif->setStatus('pending');

    // Who to notify?
    $sendToUser = null;
    if ($obligation) {
        if ($currentUser->getId() === $obligation->getCreatedBy()->getId()) {
            $sendToUser = $obligation->getRelatedTo();
        } else {
            $sendToUser = $obligation->getCreatedBy();
        }
    }

    if ($sendToUser) {
        if ($tranche->getStatus() === 'validée') {
            $newnotif->setTitle('Tranche Acceptée');
            $newnotif->setMessage(
                'La tranche de montant ' . $tranche->getAmount() .
                ' a été acceptée par ' . $currentUser->getFirstName() . ' ' . $currentUser->getLastName() . '.'
            );
        } else {
            $newnotif->setTitle('Tranche Refusée');
            $newnotif->setMessage(
                'La tranche de montant ' . $tranche->getAmount() .
                ' a été refusée par ' . $currentUser->getFirstName() . ' ' . $currentUser->getLastName() . '.'
            );
        }

        $newnotif->setUser($sendToUser);
        $em->persist($newnotif); // <-- persist the new notification entity

        // Fire FCM (safe if service is wired, see section 2)
        $this->fcmNotificationService->sendFcmDefaultNotification(
            $sendToUser,
            $newnotif->getTitle(),
            $newnotif->getMessage(),
            null
        );
    }

    $em->flush();

    return $this->json([
        'success' => true,
        'status' => $notif->getStatus(),
        'newRemainingAmount' => $newRemaining,
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
