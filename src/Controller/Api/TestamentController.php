<?php

namespace App\Controller\Api;

use App\Entity\Jeun;
use App\Entity\Location;
use App\Entity\Obligation;
use App\Entity\Relation;
use App\Entity\Testament;
use App\Entity\TestamentShare;
use App\Entity\User;
use App\Entity\NotifToSend;
use App\Services\CRUDService;
use App\Services\FcmNotificationService;
use App\Services\PdfGeneratorService;
use App\Services\UtilsService;
use App\UIBuilder\ObligationUI;
use App\UIBuilder\RelationUI;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

class TestamentController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly UtilsService $utilsService,
                                private readonly CRUDService $CRUDService, private readonly ObligationUI $obligationUI,
                                private readonly RelationUI $relationUI, private readonly FcmNotificationService $fcmNotificationService) {}

    #[Route('/load-testament')]
    public function loadTestament(Request $request): Response
    {
        $currentUser = $this->getUser();
        $testament = $this->entityManager->getRepository(Testament::class)->findOneBy([
            'createdBy' => $currentUser
        ]);
        if(is_null($testament)) {
            $testament = new Testament();
            $testament->setCreatedBy($currentUser);
            $this->entityManager->persist($testament);
            $this->entityManager->flush();
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'testament' => $this->obligationUI->getTestament($testament),
        ]);
        return $jsonResponse;
    }

    #[Route('/load-others-testament')]
    public function loadOthersTestament(Request $request): Response
    {
        $currentUser = $this->getUser();
        //others testaments
        $sharedTestaments = $this->entityManager->getRepository(TestamentShare::class)->findSharedTestaments($currentUser);
        $othersTestaments = [];
        foreach ($sharedTestaments as $sharedTestament) {
            $othersTestaments[] = $this->obligationUI->getTestament($sharedTestament->getTestament());
        }

        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'othersTestaments' => $othersTestaments
        ]);
        return $jsonResponse;
    }


    #[Route('/save-testament', methods: ['POST'])]
    public function savetestament(Request $request): Response
    {
        $testamentDatas = json_decode($request->get('testament'), true);
        $currentUser = $this->getUser();
        $testament = $this->entityManager->getRepository(Testament::class)->findOneBy([
            'createdBy' => $currentUser
        ]);
        if(is_null($testament)) {
            throw new \ErrorException( 'testament non créé');
        }
        if(isset($testamentDatas['location'])) {
            $testamentDatas['location'] = $this->utilsService->limitText($testamentDatas['location'], 500);
        }
        if(isset($testamentDatas['family'])) {
            $testamentDatas['family'] = $this->utilsService->limitText($testamentDatas['family'], 3000);
        }
        if(isset($testamentDatas['goods'])) {
            $testamentDatas['goods'] = $this->utilsService->limitText($testamentDatas['goods'], 3000);
        }
        if(isset($testamentDatas['toilette'])) {
            $testamentDatas['toilette'] = $this->utilsService->limitText($testamentDatas['toilette'], 3000);
        }
        if(isset($testamentDatas['fixe'])) {
            $testamentDatas['fixe'] = $this->utilsService->limitText($testamentDatas['fixe'], 3000);
        }
        if(isset($testamentDatas['lastwill"'])) {
            $testamentDatas['lastwill"'] = $this->utilsService->limitText($testamentDatas['lastwill"'], 3000);
        }
        $testament->setFromUI($testamentDatas);
        $this->entityManager->persist($testament);
        $this->entityManager->flush();
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData();
        return $jsonResponse;
    }


    #[Route('/load-relation-share-testatment')]
    public function loadContactsList(Request $request): Response
    {
        $currentUser = $this->getUser();
        $testamentToShare = $this->entityManager->getRepository(Testament::class)->findOneBy([
            'createdBy' => $currentUser
        ]);
        //contact
        $relations = $this->entityManager->getRepository(Relation::class)->findListOfRelations($currentUser, ['active'], 150);
        $nbRelations = $this->entityManager->getRepository(Relation::class)->countActiverRelations($currentUser);
        $relationsUI = $this->relationUI->getRelationsList($relations, $currentUser);
        //shares
        $testamentShareUserIds = $this->entityManager->getRepository(TestamentShare::class)->findShareUserIds($testamentToShare);
        $relationsWhithSharesUI = [];
        foreach ($relationsUI as $relationUI) {
            $relationUI['shareTestament'] = in_array($relationUI['user']['id'], $testamentShareUserIds);
            $relationsWhithSharesUI[] = $relationUI;
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'relations' => $relationsWhithSharesUI,
            'nbRelations' => $nbRelations,
        ]);
        return $jsonResponse;
    }

    #[Route('/validate-share-to-testatment', methods: ['POST'])]
    public function validateShareTestatement(Request $request) {
        $currentUser = $this->getUser();
        //ajout user
        $accept = $request->get('accept') == 'true';
        $relation = $this->entityManager->getRepository(Relation::class)->findOneBy([
            'id' => $request->get('relation'),
        ]);
        if(!is_null($relation)) {
            if($relation->getUserTarget()->getId() != $currentUser->getId() && $relation->getUserSource()->getId() != $currentUser->getId()) {
                throw new \ErrorException('Error relation '.$request->get('relation'));
            }
            $testamentToShare = $this->entityManager->getRepository(Testament::class)->findOneBy([
                'createdBy' => $currentUser
            ]);
            if($relation->getUserTarget()->getId() == $currentUser->getId()) {
                $userToShareWith = $relation->getUserSource();
            } else {
                $userToShareWith = $relation->getUserTarget();
            }
            $testamentShare = $this->entityManager->getRepository(TestamentShare::class)
                ->findExistingShare($testamentToShare, $userToShareWith);
            if($accept) {
                if(is_null($testamentShare)) {
                    $testamentShare = new TestamentShare();
                    $testamentShare->setUser($userToShareWith);
                    $testamentShare->setTestament($testamentToShare);
                    $this->entityManager->persist($testamentShare);
                    $this->entityManager->flush();
                    //notif
                    $title = $currentUser->getFullName();
                    $message = "Vous a partagé son testament";
                    $data['view'] = "shared_testament_view";
                    $notifToSend = new NotifToSend();
                    $notifToSend->setUser($userToShareWith);
                    $notifToSend->setTitle($title);
                    $notifToSend->setMessage($message);
                    $notifToSend->setSendAt(new \DateTime());
                    $notifToSend->setType('testament_share');
                    $notifToSend->setView($data['view']);
                    $notifToSend->setDatas(json_encode($data, JSON_UNESCAPED_UNICODE));
                    $notifToSend->setStatus('sent');
                    $notifToSend->setIsRead(false);
                    $this->entityManager->persist($notifToSend);
                    $this->fcmNotificationService->sendFcmDefaultNotification($userToShareWith, $title, $message, $data);
                    $this->entityManager->flush();
                }
            } else {
                if(!is_null($testamentShare)) {
                    $this->entityManager->remove($testamentShare);
                    $this->entityManager->flush();
                }
            }
        } else {
            throw new \ErrorException('Error relation '.$request->get('relation'));
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([]);
        return $jsonResponse;
    }


    #[Route('/load-testament-dettes')]
    public function loadTestamentJdmPreview(Request $request): Response
    {
        $currentUser = $this->getUser();
        $testament = $this->entityManager->getRepository(Testament::class)->findOneBy([
            'id' => $request->get('testament')
        ]);
        $testamentShare = $this->entityManager->getRepository(TestamentShare::class)->findOneBy([
            'testament' => $testament,
            'user' => $currentUser
        ]);
        if($testament->getCreatedBy()->getId() != $currentUser->getId() && is_null($testamentShare)) {
            throw new \ErrorException('Error testament load dettes '.$request->get('testament'));
        }
        //TODO CHECK ET VOIR AMANAS
        $jeds = $this->entityManager->getRepository(Obligation::class)
            ->findObligationsOfUser($testament->getCreatedBy(), 'jed', 'processing');
        $jedUIs = [];
        foreach ($jeds as $jed) {
            $jedUIs[] = $this->obligationUI->getObligation($jed, false, null, $testament->getCreatedBy());
        }
        $onms = $this->entityManager->getRepository(Obligation::class)->findObligationsOfUser($testament->getCreatedBy(), 'onm', 'processing');
        $onmUIs = [];
        foreach ($onms as $onm) {
            $onmUIs[] = $this->obligationUI->getObligation($onm, false, null, $testament->getCreatedBy());
        }
        $amanas = $this->entityManager->getRepository(Obligation::class)->findObligationsOfUser($testament->getCreatedBy(), 'amana', 'processing');
        $amanasUIs = [];
        foreach ($amanas as $amana) {
            $amanasUIs[] = $this->obligationUI->getObligation($amana, false, null, $testament->getCreatedBy());
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'jeds' => $jedUIs,
            'onms' => $onmUIs,
            'amanas' => $amanasUIs
        ]);
        return $jsonResponse;
    }


    #[Route('/testamenet-generate-pdf', methods: ['GET', 'POST'])]
    public function generatePdf(Request $request, KernelInterface $kernel, PdfGeneratorService $pdfService, ObligationUI $obligationUI)
    {
        $testament = $this->entityManager->getRepository(Testament::class)->findOneBy([
            'id' => $request->get('testament')
        ]);
        $logoElh = $kernel->getProjectDir() . '/public/images/logo-no-bg.png';
        $logoElhSrc = $this->getBase64Image($logoElh);
        $jeds = $this->entityManager->getRepository(Obligation::class)
            ->findObligationsOfUser($testament->getCreatedBy(), 'jed', 'processing');
        $jedUIs = [];
        foreach ($jeds as $jed) {
            $jedUIs[] = $obligationUI->getObligation($jed, false, null, $testament->getCreatedBy());
        }
        $onms = $this->entityManager->getRepository(Obligation::class)->findObligationsOfUser($testament->getCreatedBy(), 'onm', 'processing');
        $onmUIs = [];
        foreach ($onms as $onm) {
            $onmUIs[] = $obligationUI->getObligation($onm, false, null, $testament->getCreatedBy());
        }
        $amanas = $this->entityManager->getRepository(Obligation::class)->findObligationsOfUser($testament->getCreatedBy(), 'amana', 'processing');
        $amanasUIs = [];
        foreach ($amanas as $amana) {
            $amanasUIs[] = $obligationUI->getObligation($amana, false, null, $testament->getCreatedBy());
        }
        $date = new \DateTime();
        $dateString = $date->format('d-m-Y'). ' à '.$date->format('H:i');
        $jeun = $this->entityManager->getRepository(Jeun::class)->findOneBy([
            'createdBy' => $testament->getCreatedBy()
        ]);
        $jeunText = "Aucun jour à rattraper";
        if(!is_null($jeun)) {
            $jeunText = $jeun->getRemainingDaysSummary();
        }
//        return $this->render('layout/pdf.twig', [
//            'testament' => $testament,
//            'logoElhSrc' => $logoElhSrc,
//            'jeds' => $jedUIs,
//            'onms' => $onmUIs,
//            'amanas' => $amanasUIs,
//            'dateString' => $dateString,
//        ]);
        $htmlContent = $this->renderView('layout/pdf.twig', [
            'testament' => $testament,
            'logoElhSrc' => $logoElhSrc,
            'jeds' => $jedUIs,
            'onms' => $onmUIs,
            'amanas' => $amanasUIs,
            'dateString' => $dateString,
            'jeunText' => $jeunText,
        ]);
        // Generate unique filename
        $fileName = 'testament_' . $testament->getId() . uniqid() . '.pdf';
        $s3Url = $pdfService->generatePdf($htmlContent, $fileName);
        return $this->json([
            'success' => true,
            'url' => $s3Url
        ]);
    }

    public function getBase64Image($filePath)
    {
        $imageData = file_get_contents($filePath);
        return 'data:image/png;base64,' . base64_encode($imageData);

    }
}
