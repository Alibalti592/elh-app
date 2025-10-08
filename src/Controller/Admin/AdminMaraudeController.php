<?php

namespace App\Controller\Admin;

use App\Entity\Location;
use App\Entity\Maraude;
use App\Services\CRUDService;
use App\Services\UtilsService;
use App\UIBuilder\MaraudeUI;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminMaraudeController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly UtilsService $utilsService,
                                private readonly CRUDService $CRUDService, private readonly MaraudeUI $maraudeUI) {}

    #[Route('/admin/maraude', name: 'admin_maraude_list')]
    public function index(): Response
    {
        return $this->render('admin/modules/maraude/list.twig', [

        ]);
    }

    #[Route('/v-load-list-maraudes')]
    public function loadList(Request $request): Response
    {
        $crudParams = $this->CRUDService->getListParametersFromRequest($request);
        $maraudes = $this->entityManager->getRepository(Maraude::class)->findListFiltered($crudParams);
        $count = $this->entityManager->getRepository(Maraude::class)->countListFiltered($crudParams);
        $maraudeUIs = [];
        foreach ($maraudes as $maraude) {
            $maraudeUIs[] = $this->maraudeUI->getMaraude($maraude, $this->getUser());
        }
        $newMaraude = new Maraude();
        $newMaraude->setLocation(new Location());
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'maraudes' => $maraudeUIs,
            'maraudeIni' => $this->maraudeUI->getMaraude($newMaraude, $this->getUser()),
            'totalItems' => $count,
        ]);
        return $jsonResponse;
    }

    #[Route('/v-save-maraude', methods: ['POST'])]
    public function savemaraude(Request $request): Response
    {
        $maraudeDatas = json_decode($request->get('maraude'), true);
        if(!is_null($maraudeDatas['id'])) {
            $maraude = $this->entityManager->getRepository(Maraude::class)->findOneBy([
                'id' =>  $maraudeDatas['id']
            ]);
            if(is_null($maraude)) {
                throw new \ErrorException("Maraude introuvable");
            }
        } else {
            $maraude = new Maraude();
        }
        $maraudeDatas['description'] = strlen($maraudeDatas['description']) > 300 ? mb_substr($maraudeDatas['description'], 0, 300) : $maraudeDatas['description'];
        $maraudeDatas['description'] = $this->utilsService->htmlEncodeBeforeSave($maraudeDatas['description']);
        $maraude->setFromUI($maraudeDatas);
        $online = $maraudeDatas['online'] === true;
        $maraude->setOnline($online);
        $maraude->setValidated($online);
        //location
        $location = $maraude->getLocation();
        if(is_null($location)) {
            $location = new Location();
            $maraude->setLocation($location);
        }
        $locationDatas = $maraudeDatas['location'];
        $location->setFromUI($locationDatas);
        $this->entityManager->persist($maraude);
        $this->entityManager->flush();
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData();
        return $jsonResponse;
    }
}
