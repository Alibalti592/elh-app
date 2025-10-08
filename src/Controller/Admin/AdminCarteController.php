<?php

namespace App\Controller\Admin;

use App\Entity\CarteText;
use App\Entity\Todo;
use App\Services\CRUDService;
use App\Services\UtilsService;
use App\UIBuilder\TodoUI;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminCarteController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly UtilsService $utilsService,
                                private readonly CRUDService $CRUDService, private readonly TodoUI $carteUI) {}

    #[Route('/admin/carte', name: 'admin_carte_list')]
    public function index(): Response
    {
        return $this->render('admin/modules/carte/list.twig', [

        ]);
    }

    #[Route('/v-load-list-cartes-textes')]
    public function loadList(Request $request): Response
    {
        $cartes = $this->entityManager->getRepository(CarteText::class)->findAll();
        $carteUIs = [];
        /** @var CarteText $carte */
        foreach ($cartes as $carte) {
            $endName = $carte->isForOther() ? 'pour un proche' : 'pour moi même';
            $carteUIs[] = [
                'id' => $carte->getId(),
                'name' => $carte->getType(). ' '.$endName,
                'content' => $carte->getContent()
            ];
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'cartes' => $carteUIs,
        ]);
        return $jsonResponse;
    }

    #[Route('/v-save-carte', methods: ['POST'])]
    public function saveTodo(Request $request): Response
    {
        $carteDatas = json_decode($request->get('carte'), true);
        $carte = $this->entityManager->getRepository(CarteText::class)->findOneBy([
            'id' =>  $carteDatas['id']
        ]);
        if(is_null($carte)) {
            throw new \ErrorException("Carte introuvable");
        }
        $content = $carteDatas['content'];
        $carte->setContent($content);
        $this->entityManager->persist($carte);
        $this->entityManager->flush();
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData();
        return $jsonResponse;
    }

}
