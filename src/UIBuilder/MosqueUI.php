<?php
namespace App\UIBuilder;

use App\Entity\Mosque;
use App\Entity\MosqueNotifDece;
use App\Entity\PompeNotification;
use App\Services\UtilsService;
use App\Twig\UserThumbExtension;

class MosqueUI {

    public function __construct(private readonly UtilsService $utilsService, private readonly LocationUI $locationUI,
                                private readonly UserUI $userUI, private readonly DeceUI $deceUI) {
    }


    public function getMosque(Mosque $mosque, $distance = 0, $favoriteIds = []) {
        $managedBy = null;
        if($mosque->getManagedBy() != null) {
            $managedBy = $this->userUI->getUserProfilUI($mosque->getManagedBy());
        }
        return [
            'id' => $mosque->getId(),
            'name' => $mosque->getName(),
            'description' => $this->utilsService->htmlDecode($mosque->getDescription()),
            'managedBy' => $managedBy,
            'online' => $mosque->isOnline(),
            'location' => $this->locationUI->getLocation($mosque->getLocation()),
            'distance' => round($distance),
            'isFavorite' => in_array($mosque->getId(), $favoriteIds)
        ];
    }

    public function getMosqueDece(MosqueNotifDece $mosqueNotifDece) {
        $dece = $mosqueNotifDece->getDece();
        return [
            'id' => $mosqueNotifDece->getId(),
            'date' => $mosqueNotifDece->getCreatedAt()->format('d/m/Y'),
            'dece' => $this->deceUI->getDece($dece),
            'showOnPage' => $mosqueNotifDece->isShowOnPage(),
        ];
    }

}