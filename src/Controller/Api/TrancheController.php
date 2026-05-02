<?php

namespace App\Controller\Api;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Tranche;
use App\Entity\NotifToSend;
use App\Repository\TrancheRepository;
use App\Repository\ObligationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Exception\FirebaseException;
use App\Entity\User;
use App\Entity\Obligation;
use App\Services\FcmNotificationService;
use Symfony\Component\Security\Http\Attribute\IsGranted;
#[Route('/tranche')]
class TrancheController extends AbstractController
{
    private $fcmNotificationService;
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ObligationRepository $obligationRepository,
        private TrancheRepository $trancheRepository,
        private UserRepository $userRepository,
        FcmNotificationService $fcmNotificationService
    ) {
        $this->fcmNotificationService = $fcmNotificationService;
    }

    private function getUserFullName(?User $user): string
    {
        if (!$user) return 'Utilisateur';
        $fn = method_exists($user, 'getFirstName') ? (string)$user->getFirstName() : (method_exists($user, 'getFirstname') ? (string)$user->getFirstname() : '');
        $ln = method_exists($user, 'getLastName')  ? (string)$user->getLastName()  : (method_exists($user, 'getLastname')  ? (string)$user->getLastname()  : '');
        $name = trim($fn . ' ' . $ln);
        return $name !== '' ? $name : 'Utilisateur';
    }

    private function ackActionRequiredTrancheNotifications(User $user, int $trancheId): void
    {
        $pendingNotifs = $this->entityManager
            ->getRepository(NotifToSend::class)
            ->createQueryBuilder('n')
            ->where('n.user = :user')
            ->andWhere('n.type = :type')
            ->andWhere('(n.isRead = :isRead OR n.isRead IS NULL)')
            ->setParameter('user', $user)
            ->setParameter('type', 'tranche')
            ->setParameter('isRead', false)
            ->getQuery()
            ->getResult();

        foreach ($pendingNotifs as $notif) {
            $datas = json_decode($notif->getDatas() ?: '[]', true);
            if (!is_array($datas)) {
                continue;
            }

            $notifTrancheId = isset($datas['trancheId']) ? (int)$datas['trancheId'] : 0;
            $actions = $datas['actions'] ?? null;
            $isActionRequired = is_array($actions) && count($actions) > 0;

            if ($notifTrancheId !== $trancheId || !$isActionRequired) {
                continue;
            }

            $notif->setIsRead(true);
            $this->entityManager->remove($notif);
        }
    }

    private function syncObligationRefundStatus(Obligation $obligation): void
    {
        $remainingAmount = $obligation->getRemainingAmount();
        $remaining = $remainingAmount !== null
            ? (float) $remainingAmount
            : (float) $obligation->getAmount();

        if ($remaining <= 0.00001) {
            $obligation->setRemainingAmount(0.0);
            $obligation->setStatus('refund');
            return;
        }

        $obligation->setStatus('processing');
    }

  

#[Route('/tranche', methods: ['GET'])]
public function getTranches(Request $request): JsonResponse
{
    $obligationId = $request->query->get('obligationId');
    if (!$obligationId) {
        return new JsonResponse(['error' => 'Missing obligationId'], 400);
    }

    $tranches = $this->entityManager->getRepository(Tranche::class)
        ->findBy(['obligation' => $obligationId]);

    $data = [];
    foreach ($tranches as $tranche) {
        $data[] = [
            'id' => $tranche->getId(),
            'amount' => $tranche->getAmount(),
            'status' => $tranche->getStatus(),
            'paidAt' => $tranche->getPaidAt()?->format('Y-m-d'),
            'fileUrl' => $tranche->getFileUrl(),
        ];
    }

    return new JsonResponse($data);
}
    // -----------------------------
    // Création d'une tranche
    // -----------------------------
#[Route('/create', name: 'tranche_create', methods: ['POST'])]
public function create(Request $request): JsonResponse
{
    $currentUser = $this->getUser();

    // ---------- 1) Parse payload (JSON or multipart "tranche" field) ----------
    $data = null;
    $trancheJson = $request->request->get('tranche');
    if (!empty($trancheJson)) {
        $tmp = json_decode($trancheJson, true);
        if (is_array($tmp)) {
            $data = $tmp;
        }
    }
    if (!$data) {
        $raw = (string)$request->getContent();
        if ($raw !== '') {
            $tmp = json_decode($raw, true);
            if (is_array($tmp)) {
                $data = $tmp;
            }
        }
    }
    if (!is_array($data)) {
        return $this->json(['error' => 'Missing or invalid payload'], 400);
    }

    // ---------- 2) Basic validation ----------
    if (empty($data['obligationId']) || empty($data['amount']) || empty($data['paidAt'])) {
        return $this->json(['error' => 'Fields obligationId, amount, paidAt are required'], 400);
    }

    $obligation = $this->obligationRepository->find((int)$data['obligationId']);
    if (!$obligation) {
        return $this->json(['error' => 'Obligation introuvable'], 404);
    }

    $rawAmount = is_string($data['amount'])
        ? str_replace(',', '.', trim((string) $data['amount']))
        : $data['amount'];
    $requestedAmount = is_numeric($rawAmount) ? (float) $rawAmount : null;
    if ($requestedAmount === null || $requestedAmount <= 0) {
        return $this->json(['error' => 'Le montant du versement est invalide'], 400);
    }

    $remainingAmount = $obligation->getRemainingAmount();
    $currentRemaining = $remainingAmount !== null
        ? (float) $remainingAmount
        : (float) $obligation->getAmount();
    if ($requestedAmount - $currentRemaining > 0.00001) {
        return $this->json([
            'error' => 'Le montant du versement dépasse le montant restant à rembourser',
            'remainingAmount' => $currentRemaining,
            'trancheAmount' => $requestedAmount,
        ], 400);
    }

    // ---------- 3) Optional file upload (part name: "file") ----------
    /** @var UploadedFile|null $uploadedFile */
    $uploadedFile = $request->files->get('file');
    if ($uploadedFile) {
        try {
            $storage = (new Factory())
                ->withServiceAccount(\dirname(__DIR__, 3) . '/config/firebase_credentials.json')
                ->withDefaultStorageBucket('elhapp-78deb.firebasestorage.app')
                ->createStorage();

            $bucket = $storage->getBucket();
            $ext = $uploadedFile->guessExtension() ?: 'bin';
            $fileName = sprintf('tranches/%s.%s', uniqid('tr_', true), $ext);

            $bucket->upload(
                fopen($uploadedFile->getPathname(), 'r'),
                ['name' => $fileName]
            );

            $data['fileUrl'] = sprintf('https://storage.googleapis.com/%s/%s', $bucket->name(), $fileName);
        } catch (\Throwable $e) {
            // Do not fail request because of storage; just return 400 with reason
            return $this->json(['error' => 'File upload failed: '.$e->getMessage()], 400);
        }
    }

    // ---------- 4) Create Tranche ----------
    $tranche = new \App\Entity\Tranche();
    $tranche->setObligation($obligation);
    $tranche->setAmount($requestedAmount);
    if ($currentUser instanceof User) {
        // garde la trace de l'utilisateur ayant saisi le versement
        $tranche->setEmprunteur($currentUser);
    }

    try {
        $tranche->setPaidAt(new \DateTime((string)$data['paidAt']));
    } catch (\Exception $e) {
        return $this->json(['error' => 'Invalid date format for paidAt'], 400);
    }

    if (!empty($data['fileUrl'])) {
        $tranche->setFileUrl($data['fileUrl']);
    }

    $type = (string)$obligation->getType(); // 'jed' / 'onm' / etc.
    $obligationCreator = $obligation->getCreatedBy();
    $relatedToEntity   = $obligation->getRelatedTo();

    // set tranche status + update remainingAmount
    if (!$relatedToEntity) {
        $tranche->setStatus('validée');
        $newRemaining = max(0, $currentRemaining - (float)$tranche->getAmount());
        $obligation->setRemainingAmount($newRemaining);
        $this->syncObligationRefundStatus($obligation);
    } elseif ($obligationCreator && $currentUser && $currentUser->getId() === $obligationCreator->getId() && $type === 'jed') {
        $tranche->setStatus('validée');
        $newRemaining = max(0, $currentRemaining - (float)$tranche->getAmount());
        $obligation->setRemainingAmount($newRemaining);
        $this->syncObligationRefundStatus($obligation);
    } elseif ($obligationCreator && $relatedToEntity && $currentUser && $currentUser->getId() === $relatedToEntity->getId() && $type === 'onm') {
        $tranche->setStatus('validée');
        $newRemaining = max(0, $currentRemaining - (float)$tranche->getAmount());
        $obligation->setRemainingAmount($newRemaining);
        $this->syncObligationRefundStatus($obligation);
    } else {
        $tranche->setStatus('en attente');
    }

    $this->entityManager->persist($tranche);
    $this->entityManager->flush(); // tranche persisted

    // ---------- 5) Build Tranche-related notification (queued now) ----------
    $notifId = null;
    $warnings = [];

    if ($relatedToEntity) {
        // who to notify?
        $sendToUser = null;
        $title = '';
        $message = '';
        $payload = [];

        // Détermine si ce versement finalise le remboursement
        $willFullyRefund = abs($requestedAmount - $currentRemaining) < 0.00001;

        if ($obligationCreator && $currentUser && $currentUser->getId() === $obligationCreator->getId()) {
            // creator is acting
            $sendToUser = $relatedToEntity;
            if ($type === 'jed') {
                // Creditor (Hedy) acting on "Saliha owes Hedy" -> Auto-accepted
                if ($willFullyRefund) {
                    $title = '🎉 Remboursement finalisé !';
                    $message = "🤲 El hamdoulilah, " . $this->getUserFullName($currentUser) . " a saisis un versement qui solde votre dette. Elle apparaît désormais comme remboursée.";
                } else {
                    $title = 'Un nouveau remboursement partiel a été ajouté';
                    $message = "🩶Bonne nouvelle " . $this->getUserFullName($currentUser) . " vient de noter un remboursement partiel d'un emprunt convenu entre vous. Consulte-le !🤲";
                }
                $payload = [
                    'trancheId' => $tranche->getId(),
                    'obligationId' => $obligation->getId(),
                    'status' => 'accept',
                ];
            } else {
                // Debtor (Hedy) acting on "Hedy owes Saliha" -> Needs validation
                if ($willFullyRefund) {
                    $title = '🎉 Demande de versement final reçue';
                    $message = "🤲 El hamdoulilah, " . $this->getUserFullName($currentUser) . " a proposé un versement qui solde sa dette. Merci de le valider.";
                } else {
                    $title = 'Un nouveau remboursement partiel a été proposé';
                    $message = "🩶Bonne nouvelle " . $this->getUserFullName($currentUser) . " vient de noter un remboursement partiel d'un prêt convenu entre vous. Consulte-le !🤲";
                }
                $payload = [
                    'trancheId' => $tranche->getId(),
                    'obligationId' => $obligation->getId(),
                    'actions' => ['accept', 'decline'],
                ];
            }
        } else {
            // related user is acting
            $sendToUser = $obligationCreator;
            if ($type === 'onm') {
                // Creditor (Saliha) acting on "Hedy owes Saliha" -> Auto-accepted
                if ($willFullyRefund) {
                    $title = '🎉 Remboursement finalisé !';
                    $message = "🤲 El hamdoulilah, " . $this->getUserFullName($currentUser) . " a saisis un versement qui solde votre dette. Elle apparaît désormais comme remboursée.";
                } else {
                    $title = 'Un nouveau remboursement partiel a été ajouté';
                    $message = "🩶Bonne nouvelle " . $this->getUserFullName($currentUser) . " vient de noter un remboursement partiel d'un emprunt convenu entre vous. Consulte-le !🤲";
                }
                $payload = [
                    'trancheId' => $tranche->getId(),
                    'obligationId' => $obligation->getId(),
                    'status' => 'accept',
                ];
            } else {
                // Debtor (Saliha) acting on "Saliha owes Hedy" -> Needs validation
                if ($willFullyRefund) {
                    $title = '🎉 Demande de versement final reçue';
                    $message = "🤲 El hamdoulilah, " . $this->getUserFullName($currentUser) . " a proposé un versement qui solde sa dette. Merci de le valider.";
                } else {
                    $title = 'Un nouveau remboursement partiel a été proposé';
                    $message = "🩶Bonne nouvelle " . $this->getUserFullName($currentUser) . " vient de noter un remboursement partiel d'un prêt convenu entre vous. Consulte-le !🤲";
                }
                $payload = [
                    'trancheId' => $tranche->getId(),
                    'obligationId' => $obligation->getId(),
                    'actions' => ['accept', 'decline'],
                ];
            }
        }

        if ($sendToUser) {
            try {
                $notif = new NotifToSend();
                $notif->setUser($sendToUser);
                $notif->setTitle($title);
                $notif->setMessage($message);
                $notif->setDatas(json_encode($payload, JSON_UNESCAPED_UNICODE));
                $notif->setStatus('pending'); // or 'queued' if your worker picks that
                $notif->setSendAt(new \DateTime());
                $notif->setType('tranche');
                $notif->setView('tranche');
                $notif->setIsRead(false);

                $this->entityManager->persist($notif);
                $this->entityManager->flush();
                $notifId = $notif->getId();

                // Try to send immediately, but NEVER fail the request if FCM throws
             
            } catch (\Throwable $e) {
                $warnings[] = 'Notif creation failed';
            }
        }
    }

    // ---------- 6) Respond with scalars only (no entities) ----------
    return $this->json([
        'success' => true,
        'trancheId' => $tranche->getId(),
        'status' => $tranche->getStatus(),
        'remainingAmountObligation' => (float)$obligation->getRemainingAmount(),
        'fileUrl' => $tranche->getFileUrl(),
        'relatedToId' => $relatedToEntity?->getId(),
        'creatorId' => $obligationCreator?->getId(),
        'notifId' => $notifId,
        'warnings' => $warnings ?: null,
    ], 201);
}


    // -----------------------------
    // Réponse de l'emprunteur
    // -----------------------------
    #[Route('/respond', name: 'tranche_respond', methods: ['POST'])]
        #[IsGranted('IS_AUTHENTICATED_FULLY')]

    public function respond(Request $request): Response
    {
        /** @var User|null $currentUser */
        $currentUser = $this->getUser();
        if (!$currentUser) {
            return $this->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return $this->json(['error' => 'Payload invalide'], 400);
        }

        $trancheId = (int)($data['id'] ?? $data['trancheId'] ?? 0);
        if ($trancheId <= 0) {
            return $this->json(['error' => 'Id de tranche manquant'], 400);
        }

        /** @var Tranche|null $tranche */
        $tranche = $this->trancheRepository->find($trancheId);
        if (!$tranche) {
            return $this->json(['error' => 'Tranche introuvable'], 404);
        }

        $obligation = $tranche->getObligation();
        if (!$obligation) {
            return $this->json(['error' => 'Obligation introuvable'], 400);
        }

        $createdBy = $obligation->getCreatedBy();
        $relatedTo = $obligation->getRelatedTo();
        if (!$createdBy || !$relatedTo) {
            return $this->json(['error' => 'Aucun membre associé à cette obligation'], 400);
        }

        $isCreator = $currentUser->getId() === $createdBy->getId();
        $isRelated = $currentUser->getId() === $relatedTo->getId();
        if ((!$isCreator && !$isRelated) && !$this->isGranted('ROLE_ADMIN')) {
            return $this->json(['error' => 'Accès refusé'], 403);
        }

        if (strtolower(trim((string)$tranche->getStatus())) !== 'en attente') {
            return $this->json(['error' => 'Cette tranche a déjà été traitée'], 400);
        }

        $expectedApprover = null;
        $type = (string)$obligation->getType();
        if ($type === 'jed') {
            $expectedApprover = $createdBy;
        } elseif ($type === 'onm') {
            $expectedApprover = $relatedTo;
        } elseif ($type === 'amana') {
            $initiator = $tranche->getEmprunteur();
            if ($initiator instanceof User) {
                $expectedApprover = ($initiator->getId() === $createdBy->getId())
                    ? $relatedTo
                    : $createdBy;
            }
        }

        if ($expectedApprover instanceof User
            && $currentUser->getId() !== $expectedApprover->getId()
            && !$this->isGranted('ROLE_ADMIN')) {
            return $this->json(['error' => 'Seule la partie concernée peut valider cette tranche'], 403);
        }

        $response = (string)($data['response'] ?? '');
        $newRemainingAmount = (float)$obligation->getRemainingAmount();

        if ($response === 'accept') {
            $newRemainingAmount = $newRemainingAmount - (float)$tranche->getAmount();
            if ($newRemainingAmount < 0) {
                return $this->json(['error' => 'Le montant de la tranche dépasse le montant restant de l\'obligation'], 400);
            }

            $tranche->setStatus('tranche accepte');
            $obligation->setRemainingAmount($newRemainingAmount);
            $this->syncObligationRefundStatus($obligation);

            // Notification pour le dernier versement accepté
            if (abs($newRemainingAmount) < 0.00001) {
                $sendTo = $tranche->getEmprunteur();
                if ($sendTo) {
                    $notif = new NotifToSend();
                    $notif->setUser($sendTo);
                    $notif->setTitle('🎉 Versement final validé');
                    $notif->setMessage("El hamdoulilah, " . $this->getUserFullName($currentUser) . " a validé un versement qui solde ta dette. Elle apparaît désormais comme remboursée.");
                    $notif->setDatas(json_encode([
                        'trancheId' => $tranche->getId(),
                        'obligationId' => $obligation->getId(),
                        'status' => 'accept',
                    ], JSON_UNESCAPED_UNICODE));
                    $notif->setStatus('pending');
                    $notif->setSendAt(new \DateTime());
                    $notif->setType('tranche');
                    $notif->setView('tranche');
                    $notif->setIsRead(false);

                    $this->entityManager->persist($notif);
                }
            }
        } elseif ($response === 'decline') {
            $tranche->setStatus('tranche refuse');
            $this->syncObligationRefundStatus($obligation);
        } else {
            return $this->json(['error' => 'Réponse invalide'], 400);
        }

        // La tranche a été traitée depuis la vue Obligation:
        // on retire la notification d'action associée de la cloche du validateur.
        $this->ackActionRequiredTrancheNotifications($currentUser, $trancheId);

        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'status' => $tranche->getStatus(),
            'newRemainingAmount' => $newRemainingAmount,
            // backward compatibility with old typo used in app
            'newRemaingAmount' => $newRemainingAmount,
        ]);
    }
 #[Route('/update/{id}', name: 'tranche_update', methods: ['POST', 'PUT', 'PATCH'])]
public function update(Request $request, int $id): JsonResponse
{
    $currentUser = $this->getUser();
    $tranche = $this->trancheRepository->find($id);

    if (!$tranche) {
        return $this->json(['error' => 'Tranche introuvable'], 404);
    }

    $obligation = $tranche->getObligation();
    if (!$obligation) {
        return $this->json(['error' => 'Obligation introuvable pour cette tranche'], 400);
    }

    // permission check
    $isCreator = $obligation->getCreatedBy() && $currentUser && $currentUser->getId() === $obligation->getCreatedBy()->getId();
    if (!$isCreator && !$this->isGranted('ROLE_ADMIN')) {
        return $this->json(['error' => 'Accès refusé'], 403);
    }

    // --------------------------
    // Robust payload parsing
    // --------------------------
    $data = [];

    // 1) Try raw JSON body (typical for application/json)
    $content = (string) $request->getContent();
    if ($content !== '') {
        $decoded = json_decode($content, true);
        if (is_array($decoded)) {
            $data = $decoded;
        }
    }

    // 2) If not found, some multipart clients embed a JSON payload inside a form field (e.g. 'tranche' or 'payload')
    if (empty($data)) {
        $possibleJsonFields = ['tranche', 'data', 'payload', 'json', 'body'];
        foreach ($possibleJsonFields as $k) {
            if ($request->request->has($k)) {
                $maybe = $request->request->get($k);
                if (!empty($maybe)) {
                    $try = json_decode((string)$maybe, true);
                    if (is_array($try)) {
                        $data = $try;
                        break;
                    }
                }
            }
        }
    }

    // 3) Fallback to normal form fields (typical for multipart/form-data)
    if (empty($data)) {
        $post = $request->request->all();
        if (!empty($post) && is_array($post)) {
            $data = $post;
        }
    }

    // Guarantee array
    if (!is_array($data)) {
        $data = [];
    }

    // --------------------------
    // File upload (same as before) - ensure you use 'file' as part name from client
    // --------------------------
    /** @var UploadedFile|null $uploadedFile */
    $uploadedFile = $request->files->get('file');
    if ($uploadedFile) {
        try {
            $storage = (new Factory())
                ->withServiceAccount(\dirname(__DIR__, 3) . '/config/firebase_credentials.json')
                ->withDefaultStorageBucket('elhapp-78deb.firebasestorage.app')
                ->createStorage();

            $bucket = $storage->getBucket();
            $ext = $uploadedFile->guessExtension() ?: 'bin';
            $fileName = sprintf('tranches/%s.%s', uniqid('tr_', true), $ext);

            $bucket->upload(
                fopen($uploadedFile->getPathname(), 'r'),
                ['name' => $fileName]
            );

            $data['fileUrl'] = sprintf('https://storage.googleapis.com/%s/%s', $bucket->name(), $fileName);
        } catch (FirebaseException $e) {
            return $this->json(['error' => 'Firebase error: ' . $e->getMessage()], 500);
        } catch (\Throwable $e) {
            return $this->json(['error' => 'General error: ' . $e->getMessage()], 500);
        }
    }

    // --------------------------
    // Existing update logic (defensive)
    // --------------------------
    $oldAmount = (float)$tranche->getAmount();
    $oldStatus = $tranche->getStatus();

    // amount: update only if key exists and not empty string/null
    if (array_key_exists('amount', $data) && $data['amount'] !== null && $data['amount'] !== '') {
        if (is_numeric($data['amount'])) {
            $newAmount = (float)$data['amount'];
        } else {
            $maybe = str_replace(',', '.', (string)$data['amount']);
            $newAmount = is_numeric($maybe) ? (float)$maybe : $oldAmount;
        }
        $tranche->setAmount($newAmount);
    } else {
        $newAmount = $oldAmount;
    }

    // paidAt
    if (array_key_exists('paidAt', $data) && $data['paidAt'] !== null && trim((string)$data['paidAt']) !== '') {
        try {
            $tranche->setPaidAt(new \DateTime((string)$data['paidAt']));
        } catch (\Exception $e) {
            return $this->json(['error' => 'Invalid date format for paidAt'], 400);
        }
    }

    // fileUrl
    if (array_key_exists('fileUrl', $data) && $data['fileUrl'] !== null && $data['fileUrl'] !== '') {
        $tranche->setFileUrl($data['fileUrl']);
    }

    // status
    if (array_key_exists('status', $data) && $data['status'] !== null && $data['status'] !== '') {
        $newStatus = (string)$data['status'];
    } else {
        $newStatus = $oldStatus;
    }

    if (array_key_exists('emprunteurId', $data) && $data['emprunteurId'] !== null && $data['emprunteurId'] !== '') {
        $empr = $this->userRepository->find((int)$data['emprunteurId']);
        if ($empr) {
            $tranche->setEmprunteur($empr);
        }
    }

    $tranche->setStatus($newStatus);

    // recalc remaining (kept your logic)
    $reductionStatuses = ['validée', 'tranche accepte'];

    if (method_exists($obligation, 'getAmount')) {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('COALESCE(SUM(t.amount), 0)')
            ->from(\App\Entity\Tranche::class, 't')
            ->where('t.obligation = :obligation')
            ->andWhere('t.id != :currentId')
            ->andWhere('t.status IN (:statuses)')
            ->setParameter('obligation', $obligation)
            ->setParameter('currentId', $tranche->getId())
            ->setParameter('statuses', $reductionStatuses);

        try {
            $sumFromDb = (float)$qb->getQuery()->getSingleScalarResult();
        } catch (\Throwable $e) {
            $sumFromDb = null;
        }

        if ($sumFromDb !== null) {
            $sumPaid = $sumFromDb;
            if (in_array($newStatus, $reductionStatuses, true)) {
                $sumPaid += (float)$newAmount;
            }
            $total = (float)$obligation->getAmount();
            $newRemaining = max(0, $total - $sumPaid);
            $obligation->setRemainingAmount($newRemaining);
            $this->syncObligationRefundStatus($obligation);
        } else {
            // fallback delta approach
            $oldAmount = (float)$oldAmount;
            $newAmount = (float)$newAmount;
            $remaining = (float)$obligation->getRemainingAmount();

            $wasReducing = in_array($oldStatus, $reductionStatuses, true);
            $isReducing = in_array($newStatus, $reductionStatuses, true);

            if ($wasReducing && !$isReducing) {
                $remaining = $remaining + $oldAmount;
            } elseif (!$wasReducing && $isReducing) {
                $remaining = max(0, $remaining - $newAmount);
            } elseif ($wasReducing && $isReducing) {
                $delta = $newAmount - $oldAmount;
                if (abs($delta) > 0.00001) {
                    $remaining = max(0, $remaining - $delta);
                }
            }
            $obligation->setRemainingAmount($remaining);
            $this->syncObligationRefundStatus($obligation);
        }
    } else {
        // fallback delta-only logic
        $oldAmount = (float)$oldAmount;
        $newAmount = (float)$newAmount;
        $remaining = (float)$obligation->getRemainingAmount();

        $wasReducing = in_array($oldStatus, $reductionStatuses, true);
        $isReducing = in_array($newStatus, $reductionStatuses, true);

        if ($wasReducing && !$isReducing) {
            $remaining = $remaining + $oldAmount;
        } elseif (!$wasReducing && $isReducing) {
            $remaining = max(0, $remaining - $newAmount);
        } elseif ($wasReducing && $isReducing) {
            $delta = $newAmount - $oldAmount;
            if (abs($delta) > 0.00001) {
                $remaining = max(0, $remaining - $delta);
            }
        }

        $obligation->setRemainingAmount($remaining);
        $this->syncObligationRefundStatus($obligation);
    }

    $this->entityManager->flush();

      
          $relatedToEntity = $obligation->getRelatedTo();
            $obligationCreator = $obligation->getCreatedBy();
    // notification (unchanged)
    if ($relatedToEntity) {
        $sendToUser = null;
        $fromUser = null;
        if($obligationCreator && $currentUser->getId() === $obligationCreator->getId()) {
            $sendToUser = $relatedToEntity;
            $fromUser = $obligationCreator;
        } else {
            $sendToUser = $obligationCreator;
            $fromUser = $relatedToEntity;
        }
        $notif = new NotifToSend();
        $notif->setUser($sendToUser);
        $notif->setTitle("Mise à jour d'un versement");
        $notif->setMessage("Un versement lié à {$fromUser->getFirstName()} {$fromUser->getLastName()} a été mis à jour.");
        $notif->setDatas(json_encode([
            'trancheId' => $tranche->getId(),
            'obligationId' => $obligation->getId(),
            'status' => $tranche->getStatus()
        ]));
        $notif->setStatus('pending');
        $notif->setSendAt(new \DateTime());
        $notif->setType('tranche');
        $notif->setView('tranche');
        $notif->setIsRead(false);

        $this->entityManager->persist($notif);
        $this->entityManager->flush();
       
    }

    return $this->json([
        'success' => true,
        'trancheId' => $tranche->getId(),
        'status' => $tranche->getStatus(),
        'amount' => (float)$tranche->getAmount(),
        'paidAt' => $tranche->getPaidAt() ? $tranche->getPaidAt()->format('Y-m-d') : null,
        'remainingAmountObligation' => (float)$obligation->getRemainingAmount(),
        'fileUrl' => $tranche->getFileUrl(),
    ]);
}


    #[Route('/delete/{id}', name: 'tranche_delete', methods: ['DELETE'])]
public function delete(int $id): JsonResponse
{
    $currentUser = $this->getUser();
    $tranche = $this->trancheRepository->find($id);

    if (!$tranche) {
        return $this->json(['error' => 'Tranche introuvable'], 404);
    }

    $obligation = $tranche->getObligation();
    if (!$obligation) {
        return $this->json(['error' => 'Obligation introuvable pour cette tranche'], 400);
    }

    // permission check
    $isCreator = $obligation->getCreatedBy() && $currentUser && $currentUser->getId() === $obligation->getCreatedBy()->getId();
    if (!$isCreator && !$this->isGranted('ROLE_ADMIN')) {
        return $this->json(['error' => 'Accès refusé'], 403);
    }

    $amount = (float)$tranche->getAmount();
    $status = $tranche->getStatus();
    $reductionStatuses = ['validée', 'tranche accepte'];

    // adjust remaining amount if tranche was reducing
    if (in_array($status, $reductionStatuses, true)) {
        $obligation->setRemainingAmount($obligation->getRemainingAmount() + $amount);
    }
    $this->syncObligationRefundStatus($obligation);

    // adjust totalAmount of obligation
    if (method_exists($obligation, 'getTotalAmount') && method_exists($obligation, 'setTotalAmount')) {
        $currentTotal = (float)$obligation->getTotalAmount();
        $obligation->setTotalAmount(max(0, $currentTotal - $amount));
    }

    $this->entityManager->remove($tranche);
    $this->entityManager->flush();

    // send notification to creator
  $relatedToEntity = $obligation->getRelatedTo();
            $obligationCreator = $obligation->getCreatedBy();
    if ($relatedToEntity) {
        $sendToUser = null;
        $fromUser = null;
        if($obligationCreator && $currentUser->getId() === $obligationCreator->getId()) {
            $sendToUser = $relatedToEntity;
            $fromUser = $obligationCreator;
        } else {
            $sendToUser = $obligationCreator;
            $fromUser = $relatedToEntity;
        }
        $notif = new NotifToSend();
        $notif->setUser($sendToUser);
        $notif->setTitle("Un versement a été supprimé");
        $notif->setMessage("Un versement a été supprimé par {$fromUser->getFirstName()} {$fromUser->getLastName()}.");
        $notif->setDatas(json_encode([
            'trancheId' => $tranche->getId(),
            'obligationId' => $obligation->getId(),
            'status' => $tranche->getStatus()
        ]));
        $notif->setSendAt(new \DateTime());
        $notif->setType('tranche');
        $notif->setView('tranche');
        $notif->setStatus('pending');
        $notif->setIsRead(false);

        $this->entityManager->persist($notif);
        $this->entityManager->flush();
         
    }

    return $this->json([
        'success' => true,
        'message' => 'Tranche supprimée',
        'remainingAmountObligation' => $obligation->getRemainingAmount(),
        'totalAmountObligation' => $obligation->getTotalAmount() // new field in response
    ]);
}

}
