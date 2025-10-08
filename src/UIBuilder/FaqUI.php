<?php
namespace App\UIBuilder;

use App\Entity\Faq;
use App\Services\UtilsService;
use App\Twig\UserThumbExtension;

class FaqUI {

    public function __construct(private readonly UtilsService $utilsService) {

    }


    public function getFaq(Faq $faq) {
        return [
            'id' => $faq->getId(),
            'question' => $faq->getQuestion(),
            'reponse' => $this->utilsService->htmlDecode($faq->getReponse()),
            'online' => $faq->isOnline(),
        ];
    }


}