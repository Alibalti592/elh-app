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
    $data = json_decode($request->getContent(), true);

    // Validation des champs obligatoires
    $requiredFields = ['obligationId', 'emprunteurId', 'amount', 'paidAt'];
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            return $this->json(['error' => "Missing field: $field"], 400);
        }
    }

    // Récupération de l'obligation et de l'emprunteur
    $obligation = $this->obligationRepository->find($data['obligationId']);
    $emprunteur = $this->userRepository->find($data['emprunteurId']);
    $currentUser = $this->getUser();

    if (!$obligation || !$emprunteur) {
        return $this->json(['error' => 'Obligation or emprunteur not found'], 404);
    }

    // Création de la tranche
    $tranche = new Tranche();
    $tranche->setObligation($obligation);

    // Déterminer le prêteur et l’emprunteur selon le type d’obligation
    $type = $obligation->getType(); // 'jed' ou 'emprunt'

    if ($type === 'jed') {
        $preteur = $obligation->getRelatedTo();
        $emprunteur = $obligation->getCreatedBy();
    } else {
        $preteur = $obligation->getCreatedBy();
        $emprunteur = $obligation->getRelatedTo();
    }

    $tranche->setEmprunteur($emprunteur);
    $tranche->setAmount($data['amount']);

    try {
        $tranche->setPaidAt(new \DateTime($data['paidAt']));
    } catch (\Exception $e) {
        return $this->json(['error' => 'Invalid date format for paidAt'], 400);
    }

    // ✅ Ajout du champ fileUrl (optionnel)
    if (!empty($data['fileUrl'])) {
        $tranche->setFileUrl($data['fileUrl']);
    }

    // Déterminer le statut de la tranche
    if ($currentUser === $preteur) {
        // Le prêteur crée la tranche → validée directement
        $tranche->setStatus('validée');

        // Mettre à jour le montant restant de l’obligation
        $newRemaining = max(0, (float)$obligation->getRemainingAmount() - (float)$tranche->getAmount());
        $obligation->setRemainingAmount($newRemaining);
    } elseif ($currentUser === $emprunteur) {
        // L’emprunteur crée la tranche → en attente
        $tranche->setStatus('en attente');
    }

    $this->entityManager->persist($tranche);
    $this->entityManager->flush();

    // Notification seulement si l'emprunteur doit accepter
    if ($tranche->getStatus() === 'en attente') {
        $notif = new NotifToSend();
        $notif->setUser($emprunteur);
        $notif->setTitle("Nouvelle tranche proposée");
        $notif->setMessage("Une nouvelle tranche d’un montant de {$tranche->getAmount()}€ vous est proposée.");
        $notif->setDatas(json_encode([
            'trancheId' => $tranche->getId(),
            'actions' => ['accept', 'decline']
        ]));
        $notif->setSendAt(new \DateTime());
        $notif->setType('tranche');
        $notif->setView("tranche");
        $notif->setStatus('pending');

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
