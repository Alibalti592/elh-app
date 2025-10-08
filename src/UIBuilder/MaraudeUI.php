<?php
namespace App\UIBuilder;

use App\Entity\Maraude;
use App\Services\UtilsService;
use App\Twig\UserThumbExtension;

class MaraudeUI {

    public function __construct(private readonly UtilsService $utilsService, private readonly LocationUI $locationUI, ) {
    }


    public function getMaraude(Maraude $maraude, $currentUser, $distance = 0) {
        $managedBy = "";
        $isOwner = false;
        if(!is_null($maraude->getManagedBy())) {
            $managedBy = $maraude->getManagedBy()->getFullname();
            $isOwner = $maraude->getManagedBy()->getId() == $currentUser->getId();
        }
        $timeDisplay = $maraude->getDate()->format('H').'h'.$maraude->getDate()->format('i');
        $dateDisplay = $maraude->getDate()->format('d/m/Y');
        return [
            'id' => $maraude->getId(),
            'description' => $this->utilsService->htmlDecode($maraude->getDescription()),
            'online' => $maraude->isOnline(),
            'location' => $this->locationUI->getLocation($maraude->getLocation()),
            'validated' => $maraude->isValidated(),
            'distance' => $distance,
            'managedBy' => $managedBy,
            'date' => $maraude->getDate()->format('Y-m-d'),
            'dateVue' => $maraude->getDate()->format('Y-m-d, h:i'),
            'timeDisplay' => $timeDisplay,
            'datetimeDisplay' => $dateDisplay.' à '.$timeDisplay,
            'isOwner' => $isOwner
        ];
    }


}