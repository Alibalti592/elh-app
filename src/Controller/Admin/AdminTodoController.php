<?php

namespace App\Controller\Admin;

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

class AdminTodoController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly UtilsService $utilsService,
                                private readonly CRUDService $CRUDService, private readonly TodoUI $todoUI) {}

    #[Route('/admin/todo', name: 'admin_todo_list')]
    public function index(): Response
    {
        return $this->render('admin/modules/todo/list.twig', [

        ]);
    }

    #[Route('/v-load-list-todos')]
    public function loadList(Request $request): Response
    {
        $count = 0;
        if($request->get('all') == 'true') {
            $todos = $this->entityManager->getRepository(Todo::class)->findAllOrdered();
        } else {
            $crudParams = $this->CRUDService->getListParametersFromRequest($request);
            $crudParams['orderBy'] = 'ordered';
            $crudParams['sort'] = 'ASC';
            $todos = $this->entityManager->getRepository(Todo::class)->findListFiltered($crudParams);
            $count = $this->entityManager->getRepository(Todo::class)->countListFiltered($crudParams);
        }

        $todoUIs = [];
        foreach ($todos as $todo) {
            $todoUIs[] = $this->todoUI->getTodo($todo);
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'todos' => $todoUIs,
            'totalItems' => $count,
        ]);
        return $jsonResponse;
    }

    #[Route('/v-save-todo', methods: ['POST'])]
    public function saveTodo(Request $request): Response
    {
        $todoDatas = json_decode($request->get('todo'), true);
        if(!is_null($todoDatas['id'])) {
            $todo = $this->entityManager->getRepository(Todo::class)->findOneBy([
                'id' =>  $todoDatas['id']
            ]);
            if(is_null($todo)) {
                throw new \ErrorException("Todo introuvable");
            }
        } else {
            $todo = new Todo();
            $maxOrdered = $this->entityManager->getRepository(Todo::class)->findMaxOrdered() + 1;
            $todo->setOrdered($maxOrdered);
        }
        $content = $this->utilsService->htmlEncodeBeforeSave($todoDatas['content']);
        $todo->setContent($content);
        $this->entityManager->persist($todo);
        $this->entityManager->flush();
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData();
        return $jsonResponse;
    }


    #[Route('/v-delete-todo', methods: ['POST'])]
    public function deleteTodo(Request $request): Response
    {
        $todoDatas = json_decode($request->get('todo'), true);
        if(!is_null($todoDatas['id'])) {
            $todo = $this->entityManager->getRepository(Todo::class)->findOneBy([
                'id' =>  $todoDatas['id']
            ]);
            if(is_null($todo)) {
                throw new \ErrorException("Todo introuvable");
            }
            $this->entityManager->remove($todo);
            $this->entityManager->flush();
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData();
        return $jsonResponse;
    }

    #[Route('/v-save-todos-order', methods: ['POST'])]
    public function saveTodosOrder(Request $request): Response
    {
        $todosDatas = json_decode($request->get('todos'), true);
        $todosOrder = [];
        $ordered = 0;
        foreach ($todosDatas as $todosData)  {
            $todosOrder[$todosData['id']] = $ordered;
            $ordered++;
        }
        $todos = $this->entityManager->getRepository(Todo::class)->findAllOrdered();
        foreach ($todos as $todo) {
            if(isset($todosOrder[$todo->getId()])) {
                $todo->setOrdered($todosOrder[$todo->getId()]);
                $this->entityManager->persist($todo);
            }
        }

        $this->entityManager->flush();
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData();
        return $jsonResponse;
    }

}
