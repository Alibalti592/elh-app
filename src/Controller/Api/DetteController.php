<?php

namespace App\Controller\Api;

use App\Entity\Location;
use App\Entity\Obligation;
use App\Entity\Relation;
use App\Entity\Testament;
use App\Entity\TestamentShare;
use App\Entity\User;
use App\Services\CRUDService;
use App\Services\NotificationService;
use App\Services\UtilsService;
use App\UIBuilder\ObligationUI;
use App\UIBuilder\RelationUI;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Exception\FirebaseException;


class DetteController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly UtilsService $utilsService,
                                private readonly CRUDService $CRUDService, private readonly ObligationUI $obligationUI,
                                private readonly RelationUI $relationUI, private readonly NotificationService $notificationService) {}

#[Route('/load-dettes')]
public function loadDettes(Request $request): Response
{
    $currentUser = $this->getUser();
    $detteType = $request->get('detteType');
    $filter = $request->get('filter');

    $obligations = $this->entityManager->getRepository(Obligation::class)
        ->findObligationsOfUser($currentUser, $detteType, $filter);

    $detteTypeOpposite = $detteType;
    if ($detteType == 'onm') {
        $detteTypeOpposite = 'jed';
    } else if ($detteType == 'jed') {
        $detteTypeOpposite = 'onm';
    }

    $obligationsShared = $this->entityManager->getRepository(Obligation::class)
        ->findObligationsShared($currentUser, $detteTypeOpposite);

    $totalAmount = 0;
    $obligationUIs = [];

    foreach ($obligations as $obligation) {
        $obUI = $this->obligationUI->getObligation($obligation, true, $currentUser);
$obUI['remainingAmount'] = $rawRemaining;                 // may be null
$obUI['amount'] = (float)$obligation->getAmount(); 
        // Add uploaded file URL if exists
        $obUI['fileUrl'] = $obligation->getFileUrl() ?? null;

        $rawRemaining = $obligation->getRemainingAmount(); // may be null
$amount = $rawRemaining !== null ? $rawRemaining : (float)$obligation->getAmount();
        if ($filter == 'processing' || $filter == 'refund') {
            $totalAmount += floatval($amount);
        }

        $obligationUIs[] = $obUI;
    }

    $obligationSharedUIs = [];
    foreach ($obligationsShared as $obligation) {
        $obUI = $this->obligationUI->getObligation($obligation, false);
        $obUI['fileUrl'] = $obligation->getFileUrl() ?? null;
        $obligationSharedUIs[] = $obUI;
    }

    return new JsonResponse([
        'obligations' => $obligationUIs,
        'obligationsShared' => $obligationSharedUIs,
        'totalAmount' => round($totalAmount, 2)
    ]);
}


#[Route('/save-dette', methods: ['POST'])]
    public function saveObligation(
        Request $request,
        ValidatorInterface $validator,
        UserRepository $userRepository
    ): JsonResponse {
        $currentUser = $this->getUser();
        if (!$currentUser) {
            return new JsonResponse(['error' => 'Utilisateur non authentifié'], 401);
        }

        // Read payload (multipart/form-data with "obligation" json and optional file "file")
        $rawJson = $request->request->get('obligation');
        if (empty($rawJson)) {
            return new JsonResponse(['error' => 'Payload manquant: obligation'], 400);
        }

        $data = json_decode($rawJson, true);
        if (!is_array($data)) {
            return new JsonResponse(['error' => 'Payload invalide'], 400);
        }


        $obligation = null;
        $isEdit = false;

        $id = $data['id'] ?? null;
        if (!empty($id)) {
            $isEdit = true;
            $obligation = $this->entityManager->getRepository(Obligation::class)->find($id);

            // Permission check: author OR relatedTo can edit (same as legacy)
            $canEdit = true;
            if (is_null($obligation)) {
                $canEdit = false;
            } elseif ($currentUser->getId() !== $obligation->getCreatedBy()->getId()) {
                $canEdit = false;
                if (!is_null($obligation?->getRelatedTo()) && $currentUser->getId() == $obligation->getRelatedTo()?->getId()) {
                    $canEdit = true;
                }
            }
            if (!$canEdit) {
                return new JsonResponse(['error' => 'Vous n’êtes pas autorisé à modifier cette dette'], 403);
            }
        }

        if (is_null($obligation)) {
            $obligation = new Obligation();
            $obligation->setCreatedBy($currentUser);
            $obligation->setCreatedAt(new \DateTimeImmutable());
        }

    
        /** @var UploadedFile|null $uploadedFile */
        $uploadedFile = $request->files->get('file');
        $newFileUrl = null;

        if ($uploadedFile instanceof UploadedFile) {
            try {
                // Initialize Firebase Storage
                $storage = (new Factory())
                    ->withServiceAccount(\dirname(__DIR__, 3) . '/config/firebase_credentials.json')
                    ->withDefaultStorageBucket('elhapp-78deb.firebasestorage.app') // bucket name only
                    ->createStorage();

                $bucket = $storage->getBucket();

                // Build unique path
                $ext = $uploadedFile->guessExtension() ?: 'bin';
                $fileName = sprintf('obligations/%s.%s', uniqid('obh_', true), $ext);

                // Upload and make publicly readable to avoid 403 AccessDenied
                $bucket->upload(
                    fopen($uploadedFile->getPathname(), 'r'),
                    [
                        'name' => $fileName,
                        'predefinedAcl' => 'publicRead', // <-- makes object public
                    ]
                );

                $newFileUrl = sprintf('https://storage.googleapis.com/%s/%s', $bucket->name(), $fileName);

                // On EDIT, if there was an old fileUrl, try to delete it (best-effort)
                if ($isEdit && $obligation->getFileUrl()) {
                    $oldUrl = $obligation->getFileUrl();
                    $prefix = 'https://storage.googleapis.com/' . $bucket->name() . '/';
                    if (str_starts_with($oldUrl, $prefix)) {
                        $oldPath = substr($oldUrl, strlen($prefix));
                        try {
                            $bucket->object($oldPath)->delete();
                        } catch (\Throwable $e) {
                            // ignore cleanup failure
                        }
                    }
                }

                // set immediately; "setFromUI" will not override it
                $data['fileUrl'] = $newFileUrl;
            } catch (FirebaseException $e) {
                return new JsonResponse(['error' => 'Firebase error: ' . $e->getMessage()], 500);
            } catch (\Throwable $e) {
                return new JsonResponse(['error' => 'General error: ' . $e->getMessage()], 500);
            }
        }

        if (isset($data['adress'])) {
            $data['adress'] = $this->utilsService->limitText($data['adress'], 500);
        }
        if (isset($data['raison'])) {
            $data['raison'] = $this->utilsService->limitText($data['raison'], 500);
        }

        // Let legacy mapper handle the rest of fields
        // (dates, amount, status, tel, firstname, lastname, note/raison, etc.)
        $obligation->setFromUI($data, $isEdit);

        // Set related user only on CREATE (same as legacy)
        $sendNotifTo = null;
        if (!$isEdit && !empty($data['relatedUserId'])) {
 $userRelated = $this->entityManager->getRepository(User::class)->findOneBy([
                'id' => $data['relatedUserId']
            ]);           
             if ($userRelated instanceof User) {
                $obligation->setRelatedTo($userRelated);
                $sendNotifTo = $userRelated;
            }
        }

        // If fileUrl was set by upload, ensure entity has it (in case setFromUI ignores it)
        if ($newFileUrl) {
            $obligation->setFileUrl($newFileUrl);
        } elseif (!empty($data['fileUrl'])) {
            // allow preserving or setting an existing URL coming from UI
            $obligation->setFileUrl($data['fileUrl']);
        }

      
       
       

        $this->entityManager->persist($obligation);
        // persist currentUser as in legacy (if needed for counters etc.)
        $this->entityManager->persist($currentUser);
        $this->entityManager->flush();

        // Notify on CREATE (legacy behavior); add your own "updated" notif if desired
        if ($sendNotifTo) {
            $this->notificationService->notifForNewObligation($obligation);
        }

        return new JsonResponse([
            'message'      => $isEdit ? 'Dette mise à jour avec succès' : 'Dette enregistrée avec succès',
            'obligationId' => $obligation->getId(),
            'fileUrl'      => $obligation->getFileUrl(),
            'isEdit'       => $isEdit,
        ], 200);
    }

    #[Route('/delete-dette', methods: ['POST'])]
    public function deleteObligation(Request $request): Response
    {
        $currentUser = $this->getUser();
        $obligation = $this->entityManager->getRepository(Obligation::class)->findOneBy([
            'id' => $request->get('obligationId'),
        ]);
        if(!is_null($obligation)) {
            if($currentUser->getId() != $obligation->getCreatedBy()->getId() && $currentUser->getId() != $obligation->getRelatedTo()->getId()) {
                throw new \ErrorException("cant deleteObligation");
            }
            $obligation->setDeletedAt(new \DateTimeImmutable());
            $this->entityManager->persist($obligation);
            $this->entityManager->flush();
        } else {
            throw new \ErrorException("deleteObligation");
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData();
        return $jsonResponse;
    }

    #[Route('/refund-dette', methods: ['POST'])]
    public function refundObligation(Request $request): Response
    {
        $currentUser = $this->getUser();
        /** @var Obligation $obligation */
        $obligation = $this->entityManager->getRepository(Obligation::class)->findObligationCanRefund($currentUser, $request->get('obligationId'));
        if(!is_null($obligation)) {
            $notifUser = false;
            if($request->get('refundBack') == 'true') {
                $obligation->setStatus('ini');
            } else {
                $obligation->setStatus('refund');
                if(!is_null($obligation->getRelatedTo())) {
                    //notify other part
                    $notifUser = true;
                }
            }
            $this->entityManager->persist($obligation);
            $this->entityManager->flush();
            if($notifUser) {
                $this->notificationService->notifRefundDette($currentUser, $obligation);
            }
        } else {
            throw new \ErrorException("refundObligation");
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData();
        return $jsonResponse;
    }

    #[Route('/dette-set-relatedto', methods: ['POST'])]
    public function setRelatedTo(Request $request): Response {
        $currentUser = $this->getUser();
        $obligation = $this->entityManager->getRepository(Obligation::class)->findOneBy([
            'id' => $request->get('obligationId'),
            'createdBy' => $currentUser
        ]);
        $jsonResponse = new JsonResponse();
        if(!is_null($obligation)) {
            $userRelated = $this->entityManager->getRepository(User::class)->findOneBy([
                'id' => $request->get('userId')
            ]);
            if(!is_null($userRelated)) {
                $relation = $this->entityManager->getRepository(Relation::class)->findRelation($currentUser, $userRelated);
                if(!is_null($relation)) {
                    $obligation->setRelatedTo($userRelated);
                    $this->entityManager->persist($obligation);
                    $this->entityManager->flush();
                    $this->notificationService->notifForNewObligation($obligation);
                } else {
                    $jsonResponse->setStatusCode(500);
                    $jsonResponse->setData([
                        'message'   => 'Ce membre ne fait pas partie de votre communauté'
                    ]);
                }
            } else {
                $jsonResponse->setStatusCode(500);
                $jsonResponse->setData([
                    'message'   => 'Utilisateur introuvable'
                ]);
            }
        } else {
            throw new \ErrorException("relatedto err");
        }
        return $jsonResponse;
    }

    #[Route('/load-dettes-to-refund', methods: ['GET'])]
    public function loadDettesToRefund(Request $request): Response
    {
        $currentUser = $this->getUser();
        $obligations = $this->entityManager->getRepository(Obligation::class)
            ->findObligationToRefund($currentUser);
        $obligationUIs = [];
        foreach ($obligations as $obligation) {
            $obligationUIs[] = $this->obligationUI->getObligation($obligation, true, $currentUser);
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'obligations' => $obligationUIs,
        ]);
        return $jsonResponse;
    }


    #[Route('/load-all-user-dettes-not-refund', methods: ['GET'])]
    public function loadAllDettesNotRefund(Request $request): Response
    {
        $currentUser = $this->getUser();
        $obligations = $this->entityManager->getRepository(Obligation::class)
            ->findObligationNotRefund($currentUser);
        $obligationUIs = [];
        foreach ($obligations as $obligation) {
            $obligationUIs[] = $this->obligationUI->getObligation($obligation, true, $currentUser);
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'obligations' => $obligationUIs,
        ]);
        return $jsonResponse;
    }
}