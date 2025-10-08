<?php
namespace App\UIBuilder;

use App\Entity\Dece;
use App\Entity\Pompe;
use App\Entity\PompeNotification;
use App\Services\UtilsService;
use App\Twig\UserThumbExtension;

class PompeUI {

    public function __construct(private readonly UtilsService $utilsService,
                                private readonly LocationUI $locationUI,private readonly DeceUI $deceUI, private readonly UserUI $userUI ) {
    }


    public function getPompe(Pompe $pompe, $distance = 0) {
        $managedBy = "";
        $user = null;
        $fullname = $pompe->getFullname();
        if(!is_null($pompe->getManagedBy())) {
            $managedBy = $pompe->getManagedBy()->getFullname();
            if(is_null($fullname)) {
                $fullname = $managedBy;
            }
            $user = $this->userUI->getUserProfilUI($pompe->getManagedBy());
        }
        return [
            'id' => $pompe->getId(),
            'name' => $pompe->getName(),
            'description' => $this->utilsService->htmlDecode($pompe->getDescription()),
            'online' => $pompe->isOnline(),
            'location' => $this->locationUI->getLocation($pompe->getLocation()),
            'validated' => $pompe->isValidated(),
            'distance' => $distance,
            'managedBy' => $managedBy,
            'user' => $user,
            'fullname' => $fullname,
            'phone' => $pompe->getPhone(),
            'phonePrefix' => $pompe->getPhonePrefix(),
            'phoneUrgence' => $pompe->getPhoneUrgence(),
            'phoneUrgencePrefix' => $pompe->getPrefixUrgence(),
            'emailPro' => $pompe->getEmailpro(),
            'namePro' => trim($pompe->getFullname()),
        ];
    }

    public function getPompeDemand(PompeNotification $pompeNotification, $fromUser = false) {
        $pompe = $pompeNotification->getPompe();
        $dece = $pompeNotification->getDece();
        return [
            'id' => $pompeNotification->getId(),
            'pompe' => $this->getPompe($pompe),
            'date' => $pompeNotification->getCreatedAt()->format('d/m/Y'),
            'dece' => $this->deceUI->getDece($dece),
            'status' => $pompeNotification->getStatus(),
            'statusLabel' => $this->getDemandStatusLabel($pompeNotification->getStatus(), $fromUser)
        ];
    }

    public function getDemandStatusLabel($status, $fromUser = false)
    {
        if($fromUser) {
            if($status == 'canDemand') {
                return 'Demande en cours';
            } elseif ($status == 'accepted') {
                return 'Accepté';
            } elseif ($status == 'rejected') {
                return 'Ne souhaite pas vous contacter';
            }
        } else {
            if($status == 'canDemand') {
                return 'Démarrer la conversation';
            } elseif ($status == 'accepted') {
                return 'Acceptée';
            } elseif ($status == 'rejected') {
                return 'Non répondu';
            }
        }

    }

}