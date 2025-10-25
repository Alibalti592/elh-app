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
use App\Services\FcmNotificationService;
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
    $data = null;
    $trancheJson = $request->request->get('tranche');
    if (!empty($trancheJson)) {
        $data = json_decode($trancheJson, true);
        if (!is_array($data)) {
            return $this->json(['error' => 'Invalid tranche JSON'], 400);
        }
    } else {
        $raw = $request->getContent();
        if (!empty($raw)) {
            $tmp = json_decode($raw, true);
            if (is_array($tmp)) {
                $data = $tmp;
            }
        }
    }

    if (!is_array($data)) {
        return $this->json(['error' => 'Missing payload'], 400);
    }

   



    /** @var \Symfony\Component\HttpFoundation\File\UploadedFile|null $uploadedFile */
    $uploadedFile = $request->files->get('file');
    if ($uploadedFile) {
        try {
           
            $storage = (new Factory())
                ->withServiceAccount(\dirname(__DIR__, 3) . '/config/firebase_credentials.json')
                ->withDefaultStorageBucket('mc-connect-5bd22')
                ->createStorage();

            $bucket = $storage->getBucket();
            $ext = $uploadedFile->guessExtension() ?: 'bin';
            $fileName = sprintf('tranches/%s.%s', uniqid('tr_', true), $ext);

            $bucket->upload(
                fopen($uploadedFile->getPathname(), 'r'),
                [
                    'name' => $fileName,
                    // 'predefinedAcl' => 'publicRead',
                ]
            );

            $data['fileUrl'] = sprintf('https://storage.googleapis.com/%s/%s', $bucket->name(), $fileName);
        } catch (\Kreait\Firebase\Exception\FirebaseException $e) {
            return $this->json(['error' => 'Firebase error: ' . $e->getMessage()], 500);
        } catch (\Throwable $e) {
            return $this->json(['error' => 'General error: ' . $e->getMessage()], 500);
        }
    }
    $obligation = $this->obligationRepository->find((int)$data['obligationId']);

    $tranche = new \App\Entity\Tranche();
    $tranche->setObligation($obligation);
       // <-- always from payload
    $tranche->setAmount((float)$data['amount']);

    try {
        $tranche->setPaidAt(new \DateTime((string)$data['paidAt']));
    } catch (\Exception $e) {
        return $this->json(['error' => 'Invalid date format for paidAt'], 400);
    }

    if (!empty($data['fileUrl'])) {
        $tranche->setFileUrl($data['fileUrl']);
    }

    $type = $obligation->getType(); // 'jed', 'onm', etc.
   
    $obligationCreator = $obligation->getCreatedBy();
          $relatedToEntity = $obligation->getRelatedTo();

if (!$relatedToEntity) {
    $tranche->setStatus('validée');
    $newRemaining = max(
        0,
        (float)$obligation->getRemainingAmount() - (float)$tranche->getAmount()
    );
    $obligation->setRemainingAmount($newRemaining);
    if($newRemaining <= 0) {
        $obligation->setStatus('refund');
    }
} elseif ($obligationCreator && $currentUser->getId() === $obligationCreator->getId() && $type === 'jed') {
    $tranche->setStatus('validée');
    $newRemaining = max(
        0,
        (float)$obligation->getRemainingAmount() - (float)$tranche->getAmount()
    );
    $obligation->setRemainingAmount($newRemaining);
     if($newRemaining <= 0) {
        $obligation->setStatus('refund');
    }
} elseif($obligationCreator && $currentUser->getId() === $relatedToEntity->getId() && $type === 'onm') {
    $tranche->setStatus('validée');
    $newRemaining = max(
        0,
        (float)$obligation->getRemainingAmount() - (float)$tranche->getAmount()
    );
    $obligation->setRemainingAmount($newRemaining);
     if($newRemaining <= 0) {
        $obligation->setStatus('refund');
    }

} else {

    $tranche->setStatus('en attente');
}

    $this->entityManager->persist($tranche);
    $this->entityManager->flush();
  
   // --- replace your whole "if ($relatedToEntity) { ... }" block with this ---
if ($relatedToEntity) {
    $notif = new NotifToSend();

    // tiny helper (inline) to format names safely
    $fullName = function (?User $u): string {
        if (!$u) return 'Utilisateur';
        $fn = method_exists($u, 'getFirstname') ? (string)$u->getFirstname() : '';
        $ln = method_exists($u, 'getLastname') ? (string)$u->getLastname() : '';
        $name = trim($fn . ' ' . $ln);
        return $name !== '' ? $name : 'Utilisateur';
    };
    $sendToUser = null;
    // CASE 1: Someone other than the creator proposes a tranche on 'jed' -> notify creator (PENDING)
    if ($currentUser->getId() !== ($obligationCreator?->getId()) && $type === 'jed') {
        if (!$obligationCreator) {
            return $this->json(['error' => 'Créateur de l’obligation introuvable'], 500);
        }
        $sendToUser = $obligationCreator;
        $notif->setUser($obligationCreator); // <-- ENTITY, not ID
        $notif->setTitle('Un nouveau versement a été proposé');
        $notif->setMessage('Un nouveau versement d’un montant de ' . $tranche->getAmount() . '€ vous est proposé par ' . $fullName($currentUser) . '.');
        $notif->setDatas(json_encode([
            'trancheId' => $tranche->getId(),
            'actions'   => ['accept', 'decline'],
        ], JSON_UNESCAPED_UNICODE));
        $notif->setStatus('pending');

    // CASE 2: Creator adds tranche directly on 'jed' -> notify related user (ACCEPT)
    } elseif ($currentUser->getId() === ($obligationCreator?->getId()) && $type === 'jed') {
        $sendToUser = $relatedToEntity;
        $notif->setUser($relatedToEntity); // <-- ENTITY, not ID
        $notif->setTitle('Un nouveau versement a été ajouté');
        $notif->setMessage('Un nouveau versement d’un montant de ' . $tranche->getAmount() . '€ a été ajouté par ' . $fullName($currentUser) . '.');
        $notif->setDatas(json_encode([
            'trancheId' => $tranche->getId(),
            'status'    => 'accept',
        ], JSON_UNESCAPED_UNICODE));
        $notif->setStatus('pending');

    // CASE 3: Creator proposes on 'onm' -> notify related user (PENDING)
    } elseif ($currentUser->getId() === ($obligationCreator?->getId()) && $type === 'onm') {
        $sendToUser = $relatedToEntity;
        $notif->setUser($relatedToEntity); // <-- ENTITY, not ID
        $notif->setTitle('Un nouveau versement a été proposé');
        $notif->setMessage('Un nouveau versement d’un montant de ' . $tranche->getAmount() . '€ vous est proposé par ' . $fullName($currentUser) . '.');
        $notif->setDatas(json_encode([
            'trancheId' => $tranche->getId(),
            'actions'   => ['accept', 'decline'],
        ], JSON_UNESCAPED_UNICODE));
        $notif->setStatus('pending');

    // CASE 4: Fallback -> notify related user (ACCEPT)
    } else {
        $sendToUser = $relatedToEntity;
         
        $notif->setUser($relatedToEntity); // <-- ENTITY, not ID
        $notif->setTitle('Un nouveau versement a été ajouté');
        $notif->setMessage('Un nouveau versement d’un montant de ' . $tranche->getAmount() . '€ a été ajouté par ' . $fullName($currentUser) . '.');
        $notif->setDatas(json_encode([
            'trancheId' => $tranche->getId(),
            'status'    => 'accept',
        ], JSON_UNESCAPED_UNICODE));
        $notif->setStatus('pending');
    }

    $notif->setSendAt(new \DateTime());
    $notif->setType('tranche');
    $notif->setView('tranche');

    $this->entityManager->persist($notif);
    $this->entityManager->flush();
    $this->fcmNotificationService->sendFcmDefaultNotification($sendToUser, $notif->getTitle(), $notif->getMessage(),null);
}


    return $this->json([
        'success'                   => true,
        'trancheId'                 => $tranche->getId(),
        'status'                    => $tranche->getStatus(),
        'remainingAmountObligation' => $obligation->getRemainingAmount(),
        'fileUrl'                   => $tranche->getFileUrl(),
        'relatedToId'              => $relatedToEntity ,
        'creator'                  => $obligationCreator ,
        'notif'                    => $notif
    ], 201);
}

    // -----------------------------
    // Réponse de l'emprunteur
    // -----------------------------
    #[Route('/respond', name: 'tranche_respond', methods: ['POST'])]
        #[IsGranted('IS_AUTHENTICATED_FULLY')]

    public function respond(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $tranche = $this->trancheRepository->find($data['id']);

        if (!$tranche) {
            return $this->json(['error' => 'Tranche introuvable'], 404);
        }

        if ($data['response'] === 'accept') {
            $tranche->setStatus('tranche accepte');
            $message = "La tranche a été acceptée par le préteur.";
            // Mise à jour de remainingAmount
    $obligation = $tranche->getObligation();
    $newRemainingAmount = $obligation->getRemainingAmount() - $tranche->getAmount();
    if($newRemainingAmount <= 0) {
        $obligation->setStatus('refund');
    }
    $obligation->setRemainingAmount(
        $obligation->getRemainingAmount() - $tranche->getAmount()
    );

        } elseif ($data['response'] === 'decline') {
            $tranche->setStatus('tranche refuse');
            $message = "La tranche a été refusée par le préteur.";
        } else {
            return $this->json(['error' => 'Réponse invalide'], 400);
        }

        $this->entityManager->flush();

        // ---- Notification au créateur ----
        $notif = new NotifToSend();
$notif->setUser($tranche->getObligation()->getCreatedBy());
        $notif->setTitle("Réponse à un versement");
        $notif->setMessage($message);
        $notif->setDatas(json_encode([
            'trancheId' => $tranche->getId(),
            'status' => $tranche->getStatus()
        ]));
        $notif->setSendAt(new \DateTime());
        $notif->setType('tranche');
        $notif->setView("tranche");

        $this->entityManager->persist($notif);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'status' => $tranche->getStatus()
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
                ->withDefaultStorageBucket('mc-connect-5bd22')
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
            'status' => $tranche->getStatus()
        ]));
        $notif->setStatus('pending');
        $notif->setSendAt(new \DateTime());
        $notif->setType('tranche');
        $notif->setView('tranche');

        $this->entityManager->persist($notif);
        $this->entityManager->flush();
         $this->fcmNotificationService->sendFcmDefaultNotification($sendToUser, $notif->getTitle(), $notif->getMessage(),null);
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
            'status' => $tranche->getStatus()
        ]));
        $notif->setSendAt(new \DateTime());
        $notif->setType('tranche');
        $notif->setView('tranche');
        $notif->setStatus('pending');

        $this->entityManager->persist($notif);
        $this->entityManager->flush();
          $this->fcmNotificationService->sendFcmDefaultNotification($sendToUser, $notif->getTitle(), $notif->getMessage(),null);
    }

    return $this->json([
        'success' => true,
        'message' => 'Tranche supprimée',
        'remainingAmountObligation' => $obligation->getRemainingAmount(),
        'totalAmountObligation' => $obligation->getTotalAmount() // new field in response
    ]);
}

}
