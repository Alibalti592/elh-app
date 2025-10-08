<?php

namespace App\Controller\Admin;

use App\Entity\Carte;
use App\Entity\CarteShare;
use App\Entity\Obligation;
use App\Entity\Testament;
use App\Services\NotificationService;
use App\Services\PdfGeneratorService;
use App\Services\AWSEmailService;
use App\Services\FcmNotificationService;
use App\Services\PrayTimesService;
use App\UIBuilder\ObligationUI;
use App\UIBuilder\UserUI;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DashboardController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager,
                                private readonly PrayTimesService       $prayTimesService,
                                private readonly FcmNotificationService $fcmNotificationService, private UrlGeneratorInterface $urlGenerator,
                                private readonly AWSEmailService        $AWSEmailService, private readonly NotificationService $notificationService)
    {


    }

    #[Route('/test')]
    public function test(ObligationUI $obligationUI) {
        $carteShare = $this->entityManager->getRepository(CarteShare::class)->findOneBy([
            'id' => 12
        ]);
        $this->notificationService->notifForCarte($carteShare);
        die('ok');

        $obligation = $this->entityManager->getRepository(Obligation::class)->findOneBy([
            'id' => 26
        ]);
        $this->notificationService->notifForObligationEchance($obligation);
        die('ok');

        $createdBy = $carteShare->getCarte()->getCreatedBy();
        $userTarget = $carteShare->getUser();
        $userName = $createdBy->getFirstname()." ".$createdBy->getLastname();
        $title = "Carte de remerciement ";
        $message =  $userName. "vous envoie une carte de remerciement";
        $data['view'] = "carte_list_view";
        $this->fcmNotificationService->sendFcmDefaultNotification($this->getUser(), $title, $message, $data);
//        $this->fcmNotificationService->sendFcmDefaultNotification($this->getUser(), 'tes',  "rrrr", null);
        die('ok');
//        $from = 'Muslim Connect <noreply@muslim-connect.fr>';
//        $to =  'elheidiapp@gmail.com';
//        $this->AWSEmailService->sendEmailWitSNS($from, $to, 'contact@mulsim-conntect.fr', 'test envoi', 'super test');
    }

    #[Route('/dashboard', name: 'dashboard')]
    public function index(UserUI $userUI): Response
    {
//        /** @var User $currentUser */
//        $currentUser = $this->getUser();
//        //test envoi message
//        $title = 'Notif title';
//        $message = 'super notif';
//        $datas = [
//            'image' => $userUI->getUserProfilUI($currentUser)['photo']
//        ];
//        $this->fcmNotificationService->sendFcmDefaultNotification($currentUser, $title, $message, $datas);
//        $this->AWSEmailService->addEmailToQueue(null, null, null, 'test envoi bo', 'contenu du mails ...');

        return new RedirectResponse($this->urlGenerator->generate('admin_user_list'));
        return $this->render('admin/modules/dashboard.twig', [

        ]);
    }


    //TEST à mettre dans
    #[Route('/generate-pdf', methods: ['GET', 'POST'])]
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
        ]);
        // Generate unique filename
        $fileName = 'testament_' . $testament->getId() . uniqid() . '.pdf';
        $s3Url = $pdfService->generatePdf($htmlContent, $fileName);
        return $this->json([
            'success' => true,
            'download_url' => $s3Url
        ]);
    }

    public function getBase64Image($filePath)
    {
        $imageData = file_get_contents($filePath);
        return 'data:image/png;base64,' . base64_encode($imageData);

    }
}
