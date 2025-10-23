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

#[Route('/tranche')]
class TrancheController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ObligationRepository $obligationRepository,
        private TrancheRepository $trancheRepository,
        private UserRepository $userRepository
    ) {}

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

   $emprunteurId = $data['emprunteurId'] ?? null;



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
          $emprunteurEntity = $obligation->getRelatedTo();

if (!$emprunteurEntity) {
    $tranche->setStatus('validée');
    $newRemaining = max(
        0,
        (float)$obligation->getRemainingAmount() - (float)$tranche->getAmount()
    );
    $obligation->setRemainingAmount($newRemaining);
} elseif ($obligationCreator && $currentUser->getId() === $obligationCreator->getId() && $type === 'jed') {
    $tranche->setStatus('validée');
    $newRemaining = max(
        0,
        (float)$obligation->getRemainingAmount() - (float)$tranche->getAmount()
    );
    $obligation->setRemainingAmount($newRemaining);
} else {

    $tranche->setStatus('en attente');
}

    $this->entityManager->persist($tranche);
    $this->entityManager->flush();
  
   if ($emprunteurEntity) {
        $notif = new \App\Entity\NotifToSend();
        $notif->setUser($emprunteurEntity);
        $notif->setTitle("Nouvelle tranche proposée par {$obligation->getFirstname()}");
        $notif->setMessage("Une nouvelle tranche d’un montant de {$tranche->getAmount()}€ vous est proposée.");
        $notif->setDatas(json_encode([
            'trancheId' => $tranche->getId(),
            'actions'   => ['accept', 'decline'],
        ]));
        $notif->setSendAt(new \DateTime());
        $notif->setType('tranche');
        $notif->setView('tranche');
        $notif->setStatus('pending');

        $this->entityManager->persist($notif);
        $this->entityManager->flush();
    }

    return $this->json([
        'success'                   => true,
        'trancheId'                 => $tranche->getId(),
        'status'                    => $tranche->getStatus(),
        'remainingAmountObligation' => $obligation->getRemainingAmount(),
        'fileUrl'                   => $tranche->getFileUrl(),
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
        $notif->setTitle("Réponse à une tranche");
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

    // permission: only obligation creator or admin
    $isCreator = $obligation->getCreatedBy() && $currentUser && $currentUser->getId() === $obligation->getCreatedBy()->getId();
    if (!$isCreator && !$this->isGranted('ROLE_ADMIN')) {
        return $this->json(['error' => 'Accès refusé'], 403);
    }

    // parse payload (multipart 'tranche' or raw json)
    $data = null;
    $trancheJson = $request->getContent(); // ✅ contains your JSON

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
        $data = [];
    }

    // handle optional file upload (same as create)
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

    // capture old values before we change the entity
    $oldAmount = (float)$tranche->getAmount();
    $oldStatus = $tranche->getStatus();

    // update amount if provided
    if (array_key_exists('amount', $data)) {
        $newAmount = (float)$data['amount'];
        $tranche->setAmount($newAmount);
    } else {
        $newAmount = $oldAmount;
    }

    // paidAt
    if (!empty($data['paidAt'])) {
        try {
            $tranche->setPaidAt(new \DateTime((string)$data['paidAt']));
        } catch (\Exception $e) {
            return $this->json(['error' => 'Invalid date format for paidAt'], 400);
        }
    }

    // fileUrl
    if (array_key_exists('fileUrl', $data)) {
        $tranche->setFileUrl($data['fileUrl']);
    }

    // new status (if provided)
    if (array_key_exists('status', $data)) {
        $newStatus = $data['status'];
    } else {
        $newStatus = $oldStatus;
    }

    // update emprunteur if provided
    if (!empty($data['emprunteurId'])) {
        $empr = $this->userRepository->find((int)$data['emprunteurId']);
        if ($empr) {
            $tranche->setEmprunteur($empr);
        }
    }

    // set status on tranche
    $tranche->setStatus($newStatus);

    // statuses that *reduce* the obligation remaining amount
    $reductionStatuses = ['validée', 'tranche accepte'];

    // --- Preferred approach: Recalculate remaining from all tranches if obligation has an original total ---
    if (method_exists($obligation, 'getAmount')) {
        // Sum amounts of accepted/validated tranches in DB excluding the current tranche,
        // then add the current tranche's amount if its newStatus is reducing.
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('COALESCE(SUM(t.amount), 0)')
            ->from(\App\Entity\Tranche::class, 't')
            ->where('t.obligation = :obligation')
            ->andWhere('t.id != :currentId')
            ->andWhere($qb->expr()->in('t.status', ':statuses'))
            ->setParameter('obligation', $obligation)
            ->setParameter('currentId', $tranche->getId())
            ->setParameter('statuses', $reductionStatuses);

        try {
            $sumFromDb = (float)$qb->getQuery()->getSingleScalarResult();
        } catch (\Throwable $e) {
            // in case query fails for unexpected reasons, fall back to delta logic below
            $sumFromDb = null;
        }

        if ($sumFromDb !== null) {
            $sumPaid = $sumFromDb;
            if (in_array($newStatus, $reductionStatuses, true)) {
                $sumPaid += (float)$newAmount;
            }
            // get original obligation total (replace getAmount if your getter is different)
            $total = (float)$obligation->getAmount();
            $newRemaining = max(0, $total - $sumPaid);
            $obligation->setRemainingAmount($newRemaining);
        } else {
            // fallback to delta approach if DB sum failed
            $oldAmount = (float)$oldAmount;
            $newAmount = (float)$newAmount;
            $remaining = (float)$obligation->getRemainingAmount();

            $wasReducing = in_array($oldStatus, $reductionStatuses, true);
            $isReducing = in_array($newStatus, $reductionStatuses, true);

            if ($wasReducing && !$isReducing) {
                // previously reduced -> roll it back
                $remaining = $remaining + $oldAmount;
            } elseif (!$wasReducing && $isReducing) {
                // now reduces -> subtract new amount
                $remaining = max(0, $remaining - $newAmount);
            } elseif ($wasReducing && $isReducing) {
                // both reduce -> adjust by delta
                $delta = $newAmount - $oldAmount;
                if (abs($delta) > 0.00001) {
                    $remaining = max(0, $remaining - $delta);
                }
            }
            $obligation->setRemainingAmount($remaining);
        }
    } else {
        // --- Fallback / backwards-compatible delta approach (original logic) ---
        $oldAmount = (float)$oldAmount;
        $newAmount = (float)$newAmount;
        $remaining = (float)$obligation->getRemainingAmount();

        $wasReducing = in_array($oldStatus, $reductionStatuses, true);
        $isReducing = in_array($newStatus, $reductionStatuses, true);

        if ($wasReducing && !$isReducing) {
            // previously reduced the remaining amount -> roll it back
            $remaining = $remaining + $oldAmount;
        } elseif (!$wasReducing && $isReducing) {
            // now reduces the remaining amount -> subtract newAmount
            $remaining = max(0, $remaining - $newAmount);
        } elseif ($wasReducing && $isReducing) {
            // both reduce: adjust by the delta (positive delta reduces remaining, negative increases)
            $delta = $newAmount - $oldAmount;
            if (abs($delta) > 0.00001) {
                $remaining = max(0, $remaining - $delta);
            }
        }

        $obligation->setRemainingAmount($remaining);
    }

    // persist and flush everything once
    $this->entityManager->flush();

    // notification for emprunteur (unchanged)
    if ($tranche->getEmprunteur()) {
        $notif = new NotifToSend();
        $notif->setUser($tranche->getEmprunteur());
        $notif->setTitle("Mise à jour d'une tranche");
        $notif->setMessage("Une tranche liée à l'obligation #{$obligation->getId()} a été mise à jour.");
        $notif->setDatas(json_encode([
            'trancheId' => $tranche->getId(),
            'status' => $tranche->getStatus()
        ]));
        $notif->setSendAt(new \DateTime());
        $notif->setType('tranche');
        $notif->setView('tranche');

        $this->entityManager->persist($notif);
        $this->entityManager->flush();
    }

    return $this->json([
        'success' => true,
        'trancheId' => $tranche->getId(),
        'status' => $tranche->getStatus(),
        'remainingAmountObligation' => $obligation->getRemainingAmount(),
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
    $creator = $obligation->getCreatedBy();
    if ($creator) {
        $notif = new NotifToSend();
        $notif->setUser($creator);
        $notif->setTitle("Tranche supprimée");
        $notif->setMessage("Une tranche (ID: {$id}) liée à votre obligation a été supprimée.");
        $notif->setDatas(json_encode(['trancheId' => $id]));
        $notif->setSendAt(new \DateTime());
        $notif->setType('tranche');
        $notif->setView('tranche');

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
