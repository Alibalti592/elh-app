<?php

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
    public function __construct(private FcmNotificationService $fcmNotificationService)
    {}

    #[Route('/notif/{id}/respond', name: 'notif_respond', methods: ['POST'])]
    public function respondNotif(
        Request $request,
        NotifToSendRepository $notifRepo,
        EntityManagerInterface $em,
        TrancheRepository $trancheRepo,
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

        $data   = json_decode($request->getContent() ?: '[]', true) ?: [];
        $action = $data['action'] ?? null;
        if (!in_array($action, ['accept', 'decline'], true)) {
            return $this->json(['error' => 'Invalid action'], 400);
        }

        // decode once, safely
        $notifData = json_decode($notif->getDatas() ?: '[]', true) ?: [];
        $trancheId = isset($notifData['trancheId']) ? (int)$notifData['trancheId'] : null;

        if (!$trancheId) {
            return $this->json(['error' => 'trancheId manquant dans la notification'], 400);
        }

        $tranche = $trancheRepo->find($trancheId);
        if (!$tranche) {
            return $this->json(['error' => 'Tranche introuvable'], 404);
        }

        $obligation = $tranche->getObligation();
        if (!$obligation) {
            return $this->json(['error' => 'Obligation introuvable pour la tranche'], 400);
        }

        $notif->setStatus($action);

        $newRemaining = null;

        if ($action === 'accept') {
            $tranche->setStatus('validée');

            if ($tranche->getAmount() > $obligation->getRemainingAmount()) {
                $notif->setStatus('decline');
                $tranche->setStatus('refusée');
                $notif->setIsRead(true);
                $em->flush();

                return $this->json([
                    'error' => 'Montant de la tranche supérieur au montant restant de l\'obligation',
                    'remainingAmount' => $obligation->getRemainingAmount(),
                    'trancheAmount'  => $tranche->getAmount(),
                ], 400);
            }
            $notif->setIsRead(true);
            $newRemaining = max(0, (float)$obligation->getRemainingAmount() - (float)$tranche->getAmount());
            $obligation->setRemainingAmount($newRemaining);
            if ($newRemaining === 0.0) {
                $obligation->setStatus('refund');
            }
        } else {
            // decline
            $tranche->setStatus('refusée');
        }

        // build counter-party notif
        $newnotif = new NotifToSend();
        $newnotif->setSendAt(new \DateTime());
        $newnotif->setType('tranche');
        $newnotif->setView('tranche');
        $newnotif->setStatus('pending');
        $newnotif->setIsRead(false);

        // who to notify?
        $sendToUser = ($currentUser->getId() === $obligation->getCreatedBy()->getId())
            ? $obligation->getRelatedTo()
            : $obligation->getCreatedBy();

        if ($sendToUser) {
            if ($tranche->getStatus() === 'validée') {
                $newnotif->setTitle('Versement Accepté');
                $newnotif->setMessage(
                    'Le versement de montant ' . $tranche->getAmount() .
                    ' a été accepté par ' . ($currentUser->getFirstName() ?? '') . ' ' . ($currentUser->getLastName() ?? '') . '.'
                );
            } else {
                $newnotif->setTitle('Versement Refusé');
                $newnotif->setMessage(
                    'Le versement de montant ' . $tranche->getAmount() .
                    ' a été refusé par ' . ($currentUser->getFirstName() ?? '') . ' ' . ($currentUser->getLastName() ?? '') . '.'
                );
            }
            $newnotif->setDatas(json_encode([
                'trancheId' => $tranche->getId(),
                'status'    => $tranche->getStatus()
            ], JSON_UNESCAPED_UNICODE));
            $newnotif->setUser($sendToUser);
            $em->persist($newnotif);

            // Never let FCM throw a 500
          
        }

        $em->flush();

        return $this->json([
            'success'            => true,
            'status'             => $notif->getStatus(),
            'newRemainingAmount' => $newRemaining,
        ]);
    }

    #[Route('/notifs', name: 'fetch_notifications', methods: ['GET'])]
    public function fetchNotifications(
        Request $request,
        NotifToSendRepository $notifRepo
    ): Response {
        $currentUser = $this->getUser();
        if (!$currentUser) {
            return new JsonResponse(['error' => 'Utilisateur non authentifié'], 401);
        }

        $notifications = $notifRepo->findBy(
            ['user' => $currentUser, 'type' => 'tranche', 'isRead' => false],
            ['sendAt' => 'DESC']
        );

        $data = [];
        foreach ($notifications as $notif) {
            $sendAt = $notif->getSendAt();
            $data[] = [
                'id'     => $notif->getId(),
                'title'  => $notif->getTitle(),
                'message'=> $notif->getMessage(),
                'type'   => $notif->getType(),
                'datas'  => json_decode($notif->getDatas(), true),
                'view'   => $notif->getView(),
                'sendAt' => $sendAt ? $sendAt->format('Y-m-d H:i:s') : null,
                'status' => $notif->getStatus(),
            ];
        }

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

        $payload = json_decode($request->getContent() ?: '[]', true) ?: [];
        $ids     = $payload['ids'] ?? [];

        // sanitize ids
        $ids = array_values(array_unique(
            array_map('intval', array_filter($ids, static fn($v) => is_numeric($v)))
        ));

        if (!$ids) {
            return $this->json(['updated' => 0, 'ids' => []]);
        }

        // only ack notifications that belong to current user
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
            'ids'     => array_map(static fn($n) => $n->getId(), $notifs),
        ]);
    }
}
