<?php

namespace App\Controller\Admin;

use App\Entity\Page;
use App\Services\CRUDService;
use App\Services\PageCustomService;
use App\Services\UtilsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminPageController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly UtilsService $utilsService,
                                private readonly CRUDService $CRUDService) {}

    #[Route('/admin/page', name: 'admin_page_list')]
    public function index(): Response
    {
        return $this->render('admin/modules/default.twig', [
            'title' => "Pages contenu site web",
            'vueID' => 'admin-page-list'
        ]);
    }

    #[Route('/v-load-list-pages')]
    public function loadList(Request $request): Response
    {
        $crudParams = $this->CRUDService->getListParametersFromRequest($request);
        $pages = $this->entityManager->getRepository(Page::class)->findListFiltered($crudParams);
        $count = $this->entityManager->getRepository(Page::class)->countListFiltered($crudParams);
        $pageUIs = [];
        /** @var Page $page */
        foreach ($pages as $page) {
            $content = $this->utilsService->htmlDecode($page->getContent());
            $pageUIs[] = [
                'id' => $page->getId(),
                'key' => $page->getSlug(),
                'title' => $page->getSlug(),
                'content' => $content,
                'contentShort' => $this->utilsService->htmlCut($content, 350, '...', true, true),
            ];
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'pages' => $pageUIs,
            'totalItems' => $count,
        ]);
        return $jsonResponse;
    }

    #[Route('/v-save-page', methods: ['POST'])]
    public function savePage(Request $request): Response
    {
        $pageDatas = json_decode($request->get('page'), true);
        $page = $this->entityManager->getRepository(Page::class)->findOneBy([
            'id' =>  $pageDatas['id']
        ]);
        if(is_null($page)) {
            throw new \ErrorException("Page introuvable");
        }
        $content = $this->utilsService->htmlEncodeBeforeSave($pageDatas['content']);
        $page->setContent($content);
        $this->entityManager->persist($page);
        $this->entityManager->flush();
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData();
        return $jsonResponse;
    }


    #[Route('/v-load-page-content')]
    public function preview(Request $request): Response
    {
        $maillKey = $request->get('mailkey');
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            "mailContent" => $mailDatas['body'],
            "mailTitle" => $mailDatas['subject']
        ]);
        return $jsonResponse;
    }

    #[Route('/admin-ini-page')]
    public function iniPage(): Response
    {
        $pageKey = 'mentions';
        $page = $this->entityManager->getRepository(Page::class)->findOneBy([
            'slug' => $pageKey
        ]);
        if(is_null($page)) {
            $page = new Page();
            $page->setContent('À saisir');
            $page->setTitle('Mentions légales');
            $page->setSlug($pageKey);
            $this->entityManager->persist($page);
            $this->entityManager->flush();
        }
       $jsonResponse = new JsonResponse();
       $jsonResponse->setData([]);
       return $jsonResponse;

    }
}
