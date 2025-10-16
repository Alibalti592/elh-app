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
    if (!$currentUser) {
        return $this->json(['error' => 'Utilisateur non authentifié'], 401);
    }

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

   

    $obligation = $this->obligationRepository->find((int)$data['obligationId']);
    if (!$obligation) {
        return $this->json(['error' => 'Obligation not found'], 404);
    }if( (float) $obligation->getRemainingAmount - (float) $data['amount'] < 0){
        return $this->json(['error' => 'Le montant de la tranche dépasse le montant restant de l\'obligation'], 400);
    }

   $emprunteurId = $data['emprunteurId'] ?? null;

if ($emprunteurId !== null && $emprunteurId !== '') {
    $emprunteurEntity = $this->userRepository->find((int) $emprunteurId);
} else {
    $emprunteurEntity = null;
}
    

    /** @var \Symfony\Component\HttpFoundation\File\UploadedFile|null $uploadedFile */
    $uploadedFile = $request->files->get('file');
    if ($uploadedFile) {
        try {
            $projectDir = $this->getParameter('kernel.project_dir');
            $storage = (new \Kreait\Firebase\Factory())
                ->withServiceAccount($projectDir . '/config/firebase_credentials.json')
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

    $tranche = new \App\Entity\Tranche();
    $tranche->setObligation($obligation);
    $tranche->setEmprunteur($emprunteurEntity);          // <-- always from payload
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
    if ($type === 'jed') {
        $preteurEntity = $obligation->getRelatedTo();
    } else {
        $preteurEntity = $obligation->getCreatedBy();
    }

    if ($preteurEntity && $currentUser->getId() === $preteurEntity->getId()) {
        // Preteur creates -> validated
        $tranche->setStatus('validée');
        $newRemaining = max(
            0,
            (float)$obligation->getRemainingAmount() - (float)$tranche->getAmount()
        );
        $obligation->setRemainingAmount($newRemaining);
    } else {
        // Emprunteur creates -> pending
        $tranche->setStatus('en attente');
    }

    $this->entityManager->persist($tranche);
    $this->entityManager->flush();

   if ($tranche->getStatus() === 'en attente' && $emprunteurEntity) {
        $notif = new \App\Entity\NotifToSend();
        $notif->setUser($emprunteurEntity);
        $notif->setTitle("Nouvelle tranche proposée");
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
    
}
