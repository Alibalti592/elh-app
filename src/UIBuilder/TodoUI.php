<?php
namespace App\UIBuilder;

use App\Entity\Todo;
use App\Services\UtilsService;
use App\Twig\UserThumbExtension;

class TodoUI {

    public function __construct(private readonly UtilsService $utilsService) {}


    public function getTodo(Todo $todo) {
        $content = $this->utilsService->htmlDecode($todo->getContent());
        $contentShort = $this->utilsService->htmlCut($content, 100);
        return [
            'id' => $todo->getId(),
            'content' => $content,
            'contentShort' => $contentShort,
            'ordered' => $todo->getOrdered(),
        ];
    }


}