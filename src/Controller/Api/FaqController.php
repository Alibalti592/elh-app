<?php

namespace App\Controller\Api;

use App\Entity\Faq;
use App\Entity\Page;
use App\Services\CRUDService;
use App\Services\UtilsService;
use App\UIBuilder\FaqUI;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FaqController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly UtilsService $utilsService,
                                private readonly CRUDService $CRUDService, private readonly FaqUI $faqUI) {}

    #[Route('/load-faqs')]
    public function loadList(Request $request): Response
    {
        $faqs = $this->entityManager->getRepository(Faq::class)->findFaqs();
        $faqUIs = [];
        foreach ($faqs as $faq) {
            $faqUIs[] = $this->faqUI->getFaq($faq);
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'faqs' => $faqUIs,
        ]);
        return $jsonResponse;
    }

    #[Route('/load-qsn')]
    public function loadQsn(Request $request): Response
    {
        $qsn = $this->entityManager->getRepository(Page::class)->findOneBy(['slug' => 'qui-sommes-nous']);
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'content' => $this->utilsService->htmlDecode($qsn->getContent()),
        ]);
        return $jsonResponse;
    }

}
