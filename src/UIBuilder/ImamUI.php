<?php
namespace App\UIBuilder;

use App\Entity\Imam;
use App\Services\UtilsService;
use App\Twig\UserThumbExtension;

class ImamUI {

    public function __construct(private readonly UtilsService $utilsService, private readonly LocationUI $locationUI) {
    }


    public function getImam(Imam $imam, $distance = 0) {
        return [
            'id' => $imam->getId(),
            'name' => $imam->getName(),
            'description' => $this->utilsService->htmlDecode($imam->getDescription()),
            'online' => $imam->isOnline(),
            'location' => $this->locationUI->getLocation($imam->getLocation()),
            'distance' => round($distance),
        ];
    }


}