<?php

namespace App\Controller\Admin;

use App\Entity\Faq;
use App\Services\CRUDService;
use App\Services\UtilsService;
use App\UIBuilder\FaqUI;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminFaqController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly UtilsService $utilsService,
                                private readonly CRUDService $CRUDService, private readonly FaqUI $faqUI) {}

    #[Route('/admin/faq', name: 'admin_faq_list')]
    public function index(): Response
    {
        return $this->render('admin/modules/faq/list.twig', [

        ]);
    }

    #[Route('/v-load-list-faqs')]
    public function loadList(Request $request): Response
    {
        $crudParams = $this->CRUDService->getListParametersFromRequest($request);
        $faqs = $this->entityManager->getRepository(Faq::class)->findListFiltered($crudParams);
        $count = $this->entityManager->getRepository(Faq::class)->countListFiltered($crudParams);
        $faqUIs = [];
        foreach ($faqs as $faq) {
            $faqUIs[] = $this->faqUI->getFaq($faq);
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'faqs' => $faqUIs,
            'totalItems' => $count,
        ]);
        return $jsonResponse;
    }

    #[Route('/v-save-faq', methods: ['POST'])]
    public function saveFAQ(Request $request): Response
    {
        $faqDatas = json_decode($request->get('faq'), true);
        if(!is_null($faqDatas['id'])) {
            $faq = $this->entityManager->getRepository(Faq::class)->findOneBy([
                'id' =>  $faqDatas['id']
            ]);
            if(is_null($faq)) {
                throw new \ErrorException("FAQ introuvable");
            }
        } else {
            $faq = new Faq();
        }
        $reponse = $this->utilsService->htmlEncodeBeforeSave($faqDatas['reponse']);
        $faq->setQuestion($faqDatas['question']);
        $faq->setReponse($reponse);
        $online = $faqDatas['online'] === true;
        $faq->setOnline($online);
        $this->entityManager->persist($faq);
        $this->entityManager->flush();
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData();
        return $jsonResponse;
    }
}
