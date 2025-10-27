<?php
// src/Controller/Api/NotifController.php
namespace App\Controller\Api;

use App\Entity\NotifToSend;
use App\Repository\NotifToSendRepository;
use App\Repository\TrancheRepository;
use App\Services\FcmNotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NotifController extends AbstractController
{
    public function __construct(private FcmNotificationService $fcmNotificationService) {}

    #[Route('/notif/{id}/respond', name: 'notif_respond', methods: ['POST'])]
    public function respondNotif(
        Request $request,
        NotifToSendRepository $notifRepo,
        TrancheRepository $trancheRepo,
        EntityManagerInterface $em,
        int $id
    ): JsonResponse {
        $currentUser = $this->getUser();
        if (!$currentUser) {
            return $this->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        $notif = $notifRepo->find($id);
        if (!$notif) {
            return $this->json(['error' => 'Notification not found'], 404);
        }

        $data   = json_decode($request->getContent() ?: '{}', true) ?? [];
        $action = $data['action'] ?? null;
        if (!in_array($action, ['accept', 'decline'], true)) {
            return $this->json(['error' => 'Invalid action'], 400);
        }

        // Decode once and reuse in both branches
        $notifData = json_decode($notif->getDatas() ?: '{}', true) ?? [];
        $trancheId = $notifData['trancheId'] ?? null;
        if (!$trancheId) {
            return $this->json(['error' => 'trancheId manquant dans la notification'], 400);
        }

        $tranche = $trancheRepo->find((int)$trancheId);
        if (!$tranche) {
            return $this->json(['error' => 'Tranche introuvable'], 404);
        }

        $obligation = $tranche->getObligation();
        if (!$obligation) {
            return $this->json(['error' => 'Obligation introuvable pour cette tranche'], 404);
        }

        $notif->setStatus($action);
        $newRemaining = null;

        if ($action === 'accept') {
            $remaining = (float)$obligation->getRemainingAmount();
            $amount    = (float)$tranche->getAmount();

            if ($amount > $remaining) {
                $notif->setStatus('decline');
                $tranche->setStatus('refusée');
                $em->flush();

                return $this->json([
                    'error' => 'Montant de la tranche supérieur au montant restant de l\'obligation'
                ], 400);
            }

            $tranche->setStatus('validée');
            $newRemaining = max(0.0, $remaining - $amount);
            $obligation->setRemainingAmount($newRemaining);
            if ($newRemaining <= 0.0) {
                $obligation->setStatus('refund');
            }
        } else {
            $tranche->setStatus('refusée');
        }

        // Mirror notification to the other party
        $newnotif = new NotifToSend();
        $newnotif->setDatas(json_encode([
            'trancheId' => $tranche->getId(),
            'status'    => $tranche->getStatus(),
        ], JSON_UNESCAPED_UNICODE));
        $newnotif->setSendAt(new \DateTimeImmutable());
        $newnotif->setType('tranche');
        $newnotif->setView('tranche');
        $newnotif->setStatus('pending');

        // Send to the counterparty
        if ($currentUser->getId() === $obligation->getCreatedBy()->getId()) {
            $newnotif->setUser($obligation->getRelatedTo());
        } else {
            $newnotif->setUser($obligation->getCreatedBy());
        }

        if ($tranche->getStatus() === 'validée') {
            $newnotif->setTitle('Tranche Acceptée');
            $newnotif->setMessage('La tranche de montant '.$tranche->getAmount().' a été acceptée par '.$currentUser->getFirstName().' '.$currentUser->getLastName().'.');
        } else {
            $newnotif->setTitle('Tranche Refusée');
            $newnotif->setMessage('La tranche de montant '.$tranche->getAmount().' a été refusée par '.$currentUser->getFirstName().' '.$currentUser->getLastName().'.');
        }

        $em->persist($newnotif);
        $em->flush();

        // Push after DB is consistent
        $this->fcmNotificationService->sendFcmDefaultNotification(
            $newnotif->getUser(),
            $newnotif->getTitle(),
            $newnotif->getMessage(),
            null
        );

        return $this->json([
            'success'            => true,
            'status'             => $notif->getStatus(),
            'newRemainingAmount' => $newRemaining,
        ]);
    }

    #[Route('/notifs', name: 'fetch_notifications', methods: ['GET'])]
    public function fetchNotifications(Request $request, NotifToSendRepository $notifRepo): Response
    {
        $currentUser = $this->getUser();
        if (!$currentUser) {
            return new JsonResponse(['error' => 'Utilisateur non authentifié'], 401);
        }

        $notifications = $notifRepo->findBy(
            ['user' => $currentUser, 'type' => 'tranche', 'status' => 'pending'],
            ['sendAt' => 'DESC']
        );

        $data = array_map(function (NotifToSend $notif) {
            return [
                'id'     => $notif->getId(),
                'title'  => $notif->getTitle(),
                'message'=> $notif->getMessage(),
                'type'   => $notif->getType(),
                'view'   => $notif->getView(),
                'sendAt' => $notif->getSendAt()?->format('Y-m-d H:i:s'),
                'status' => $notif->getStatus(),
            ];
        }, $notifications);

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

        $payload = json_decode($request->getContent() ?: '[]', true) ?? [];
        $ids     = $payload['ids'] ?? [];
        $ids     = array_values(array_unique(array_map('intval', array_filter($ids, 'is_numeric'))));

        if (empty($ids)) {
            return $this->json(['updated' => 0, 'ids' => []]);
        }

        $qb = $notifRepo->createQueryBuilder('n');
        $notifs = $qb
            ->where($qb->expr()->in('n.id', ':ids'))
            ->andWhere('n.user = :user')
            ->setParameter('ids', $ids)
            ->setParameter('user', $currentUser)
            ->getQuery()
            ->getResult();

        foreach ($notifs as $n) {
            $n->setStatus('validée');
        }

        $em->flush();

        return $this->json([
            'updated' => count($notifs),
            'ids'     => array_map(fn($n) => $n->getId(), $notifs),
        ]);
    }
}
