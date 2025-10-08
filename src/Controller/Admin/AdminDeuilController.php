<?php
namespace App\Controller\Admin;

use App\Entity\Deuil;
use App\Services\UtilsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminDeuilController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly UtilsService $utilsService) {}

    #[Route('/admin/deuil', name: 'admin_deuil')]
    public function index(): Response {
        return $this->render('admin/modules/default.twig', [
            'title' => "Période de deuil pour l'épouse",
            'vueID' => 'admin-deuil'
        ]);
    }

    #[Route('/v-load-deuil')]
    public function loadDeuil(Request $request): Response
    {
        /** @var Deuil $deuil */
        $deuils = $this->entityManager->getRepository(Deuil::class)->loadAllDeuils();
        if(count($deuils) == 1) { //ini 2
            $deuils[0]->setType('family');
            $this->entityManager->persist($deuils[0]);
            $this->entityManager->flush();
            $this->iniDeuils();
        }
        $jsonResponse = new JsonResponse();
        $startDate = new \DateTime();
        $endDate = new \DateTime();
        $endDate->modify('+4 months');
        $endDate->modify('+10 days');
        $endDateNormal = new \DateTime();
        $endDateNormal->modify('+3 days');
        $endDateDisplay = $this->utilsService->getReadableDate($endDate);
        $endDate2Display = $this->utilsService->getReadableDate($endDateNormal);
        $deuilsUI = [];
        $deuilsEditUI = [];
        foreach ($deuils as $deuil) {
            $basetext = $this->utilsService->htmlDecode($deuil->getContent());
            $content = str_replace("{date_plus_trois_jour}", $endDate2Display, $basetext);
            $content = str_replace("{datefin}", $endDateDisplay, $content);
            $deuilsUI[] = [
                'id' => $deuil->getId(),
                'type' => $deuil->getType(),
                'content' =>$content
            ];
            $deuilsEditUI[] = [
                'id' => $deuil->getId(),
                'type' => $deuil->getType(),
                'content' => $basetext
            ];
        }

        $jsonResponse->setData([
            'deuils' => $deuilsUI,
            'deuilsEdit' => $deuilsEditUI,
            'startDateDisplay' => $startDate->format('d/m/Y'),
        ]);
        return $jsonResponse;
    }

    #[Route('/v-save-deuil', methods: ['POST'])]
    public function saveFAQ(Request $request): Response
    {

        $deuilsDatas = json_decode($request->get('deuils'), true);
        foreach ($deuilsDatas as $deuilData) {
            $deuil = $this->entityManager->getRepository(Deuil::class)->findOneBy([
                'id' => $deuilData['id']
            ]);
            $content = $this->utilsService->htmlEncodeBeforeSave($deuilData['content']);
            $deuil->setContent($content);
            $this->entityManager->persist($deuil);
            $this->entityManager->flush();
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData();
        return $jsonResponse;
    }


    public function iniDeuils()
    {
        $deuil = new Deuil();
        $deuil->setContent("à saisir");
        $deuil->setType("epouse");
        $this->entityManager->persist($deuil);
        $this->entityManager->flush();

        $deuil = new Deuil();
        $deuil->setContent("à saisir");
        $deuil->setType("enceinte");
        $this->entityManager->persist($deuil);
        $this->entityManager->flush();
    }
}
