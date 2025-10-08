<?php

namespace App\Controller\Admin;

use App\Entity\NavPageContent;
use App\Entity\Page;
use App\Services\CRUDService;
use App\Services\PageCustomService;
use App\Services\S3Service;
use App\Services\UploadMediaService;
use App\Services\UtilsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminNavPageController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly UtilsService $utilsService,
                                private readonly CRUDService $CRUDService, private readonly S3Service $s3Service, private readonly UploadMediaService $uploadMediaService) {}

    #[Route('/admin/nav-app-page', name: 'admin_navpages_list')]
    public function index(): Response
    {
        return $this->render('admin/modules/default.twig', [
            'title' => "Pages Nav App",
            'vueID' => 'admin-navpage-list'
        ]);
    }

    #[Route('/v-load-list-nav-pages')]
    public function loadList(Request $request): Response
    {
        $crudParams = $this->CRUDService->getListParametersFromRequest($request);
        $pages = $this->entityManager->getRepository(NavPageContent::class)->findListFiltered($crudParams);
        $count = $this->entityManager->getRepository(NavPageContent::class)->countListFiltered($crudParams);
        $pageUIs = [];
        /** @var NavPageContent $page */
        foreach ($pages as $page) {
            $content = $this->utilsService->htmlDecode($page->getContent());
            $image = [
                'file' => null,
                'fileString' => null,
                'src' => null,
            ];;
            if(!is_null($page->getImage())) {
//                $existingImageLink = $this->s3Service->getTemporaryFileLink($page->getImage());
                $existingImageLink = $this->s3Service->getURLFromMedia($page->getImage());
                $image = [
                    'file' => null,
                    'fileString' => null,
                    'src' => $existingImageLink,
                ];
            }
            $pageUIs[] = [
                'id' => $page->getId(),
                'key' => $page->getSlug(),
                'title' => $page->getTitle(),
                'video' => $page->getVideo(),
                'image' => $image,
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

    #[Route('/v-save-nav-page', methods: ['POST'])]
    public function savePage(Request $request): Response
    {
        $pageDatas = json_decode($request->get('page'), true);
        $page = $this->entityManager->getRepository(NavPageContent::class)->findOneBy([
            'id' =>  $pageDatas['id']
        ]);
        if(is_null($page)) {
            throw new \ErrorException("Page nav introuvable");
        }
        $content = $this->utilsService->htmlEncodeBeforeSave($pageDatas['content']);
        $page->setContent($content);
        $page->setVideo($pageDatas['video']);
        $uploadOptions['maxWidth'] = 600;
        $uploadOptions['maxHeight'] = 400;
        $uploadOptions['crop'] = false;
        $uploadOptions['acl'] = 'public-read';
        if(!is_null($page->getImage()) && is_null($pageDatas['image']['fileString']) && !$pageDatas['image']['src']) {
            //Delete
            $existingDocument = $page->getImage();
            $this->s3Service->deleteFileFromMedia($existingDocument);
            $page->setImage(null);
            $this->entityManager->remove($existingDocument);
        } else { //update or add
            $media =  $this->uploadMediaService->uploadDocument($page->getImage(), $pageDatas['image'], 'image_navpage',
                'navimage', $uploadOptions);
            if($media) {
                $page->setImage($media);
            }
        }


        $this->entityManager->persist($page);
        $this->entityManager->flush();
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData();
        return $jsonResponse;
    }


    #[Route('/v-load-nav-page-content')]
    public function preview(Request $request): Response
    {

    }


    #[Route('/admin-ini-nav-pages')]
    public function iniPage(): Response
    {
        $pageKey = 'dette';
        $page = $this->entityManager->getRepository(NavPageContent::class)->findOneBy([
            'slug' => $pageKey
        ]);
        if(is_null($page)) {
            $page = new NavPageContent();
            $page->setContent('À saisir');
            $page->setTitle('Dette');
            $page->setSlug($pageKey);
            $this->entityManager->persist($page);
            $this->entityManager->flush();
        }
        $pageKey = 'deuil';
        $page = $this->entityManager->getRepository(NavPageContent::class)->findOneBy([
            'slug' => $pageKey
        ]);
        if(is_null($page)) {
            $page = new NavPageContent();
            $page->setContent('À saisir');
            $page->setTitle('Deuil');
            $page->setSlug($pageKey);
            $this->entityManager->persist($page);
            $this->entityManager->flush();
        }
        $pageKey = 'pray';
        $page = $this->entityManager->getRepository(NavPageContent::class)->findOneBy([
            'slug' => $pageKey
        ]);
        if(is_null($page)) {
            $page = new NavPageContent();
            $page->setContent('À saisir');
            $page->setTitle('Prière');
            $page->setSlug($pageKey);
            $this->entityManager->persist($page);
            $this->entityManager->flush();
        }
        $pageKey = 'don';
        $page = $this->entityManager->getRepository(NavPageContent::class)->findOneBy([
            'slug' => $pageKey
        ]);
        if(is_null($page)) {
            $page = new NavPageContent();
            $page->setContent('À saisir');
            $page->setTitle('Don');
            $page->setSlug($pageKey);
            $this->entityManager->persist($page);
            $this->entityManager->flush();
        }

        $pageKey = 'bidha';
        $page = $this->entityManager->getRepository(NavPageContent::class)->findOneBy([
            'slug' => $pageKey
        ]);
        if(is_null($page)) {
            $page = new NavPageContent();
            $page->setContent('À saisir');
            $page->setTitle('BIDHA/SUNNAH');
            $page->setSlug($pageKey);
            $this->entityManager->persist($page);
            $this->entityManager->flush();
        }

        $pageKey = 'prep-salat';
        $page = $this->entityManager->getRepository(NavPageContent::class)->findOneBy([
            'slug' => $pageKey
        ]);
        if(is_null($page)) {
            $page = new NavPageContent();
            $page->setContent('À saisir');
            $page->setTitle('PREPARER SALAT JANAZA');
            $page->setSlug($pageKey);
            $this->entityManager->persist($page);
            $this->entityManager->flush();
        }

        $pageKey = 'learn_pray';
        $page = $this->entityManager->getRepository(NavPageContent::class)->findOneBy([
            'slug' => $pageKey
        ]);
        if(is_null($page)) {
            $page = new NavPageContent();
            $page->setContent('À saisir');
            $page->setTitle('Apprendre la priere pour débutant');
            $page->setSlug($pageKey);
            $this->entityManager->persist($page);
            $this->entityManager->flush();
        }

        $pageKey = 'learn_salat';
        $page = $this->entityManager->getRepository(NavPageContent::class)->findOneBy([
            'slug' => $pageKey
        ]);
        if(is_null($page)) {
            $page = new NavPageContent();
            $page->setContent('À saisir');
            $page->setTitle('Apprendre Salat al-janaza');
            $page->setSlug($pageKey);
            $this->entityManager->persist($page);
            $this->entityManager->flush();
        }

        $pageKey = 'learn_sourat';
        $page = $this->entityManager->getRepository(NavPageContent::class)->findOneBy([
            'slug' => $pageKey
        ]);
        if(is_null($page)) {
            $page = new NavPageContent();
            $page->setContent('À saisir');
            $page->setTitle('Sourate facile a apprendre');
            $page->setSlug($pageKey);
            $this->entityManager->persist($page);
            $this->entityManager->flush();
        }

        $pageKey = 'duha';
        $page = $this->entityManager->getRepository(NavPageContent::class)->findOneBy([
            'slug' => $pageKey
        ]);
        if(is_null($page)) {
            $page = new NavPageContent();
            $page->setContent('À saisir');
            $page->setTitle('Invocations Duha');
            $page->setSlug($pageKey);
            $this->entityManager->persist($page);
            $this->entityManager->flush();
        }

        $pageKey = 'herite';
        $page = $this->entityManager->getRepository(NavPageContent::class)->findOneBy([
            'slug' => $pageKey
        ]);
        if(is_null($page)) {
            $page = new NavPageContent();
            $page->setContent('À saisir');
            $page->setTitle('Héritage');
            $page->setSlug($pageKey);
            $this->entityManager->persist($page);
            $this->entityManager->flush();
        }


        $pageKey = 'ramadan';
        $page = $this->entityManager->getRepository(NavPageContent::class)->findOneBy([
            'slug' => $pageKey
        ]);
        if(is_null($page)) {
            $page = new NavPageContent();
            $page->setContent('À saisir');
            $page->setTitle('Ramadan');
            $page->setSlug($pageKey);
            $this->entityManager->persist($page);
            $this->entityManager->flush();
        }

       $jsonResponse = new JsonResponse();
       $jsonResponse->setData([]);
       return $jsonResponse;
    }

    #[Route('/admin-ini-new-nav-pages')]
    public function iniNewPage(): Response
    {

        $pageKey = 'puit';
        $page = $this->entityManager->getRepository(NavPageContent::class)->findOneBy([
            'slug' => $pageKey
        ]);
        if(is_null($page)) {
            $page = new NavPageContent();
            $page->setContent('À saisir contenu offir un puit');
            $page->setTitle('Ramadan');
            $page->setSlug($pageKey);
            $this->entityManager->persist($page);
            $this->entityManager->flush();
        }

        $pageKey = 'offerCoran';
        $page = $this->entityManager->getRepository(NavPageContent::class)->findOneBy([
            'slug' => $pageKey
        ]);
        if(is_null($page)) {
            $page = new NavPageContent();
            $page->setContent('À saisir contenu offir un Coran');
            $page->setTitle('Ramadan');
            $page->setSlug($pageKey);
            $this->entityManager->persist($page);
            $this->entityManager->flush();
        }
        $pageKey = 'buildMosque';
        $page = $this->entityManager->getRepository(NavPageContent::class)->findOneBy([
            'slug' => $pageKey
        ]);
        if(is_null($page)) {
            $page = new NavPageContent();
            $page->setContent('À saisir contenu consgtruire une mosquée');
            $page->setTitle('Ramadan');
            $page->setSlug($pageKey);
            $this->entityManager->persist($page);
            $this->entityManager->flush();
        }
        $pageKey = 'hajiProcur';
        $page = $this->entityManager->getRepository(NavPageContent::class)->findOneBy([
            'slug' => $pageKey
        ]);
        if(is_null($page)) {
            $page = new NavPageContent();
            $page->setContent('À saisir contenu Urma/ hajj par procuratio');
            $page->setTitle('Ramadan');
            $page->setSlug($pageKey);
            $this->entityManager->persist($page);
            $this->entityManager->flush();
        }

        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([]);
        return $jsonResponse;
    }

}
