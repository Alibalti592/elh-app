<?php

namespace App\Controller\Api;

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

class TodoController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly UtilsService $utilsService,
                                private readonly CRUDService $CRUDService, private readonly TodoUI $todoUI) {}

    #[Route('/load-todos')]
    public function loadList(Request $request): Response
    {
        $todos = $this->entityManager->getRepository(Todo::class)->findAllOrdered();
        $todoUIs = [];
        foreach ($todos as $todo) {
            $todoUIs[] = $this->todoUI->getTodo($todo);
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'todos' => $todoUIs,
        ]);
        return $jsonResponse;
    }

}
