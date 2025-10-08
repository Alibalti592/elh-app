<?php
namespace App\UIBuilder;

use App\Entity\Dece;
use App\Services\UtilsService;
use App\Twig\UserThumbExtension;

class DeceUI {

    public function __construct(private readonly UtilsService $utilsService, private readonly LocationUI $locationUI) {}

    const afiliatons = [
         'sister' => 'Notre Sœur', 'bro' => "Notre Frère"
    ];
    const lieux = [ 'maison' => 'Maison',  'hoptial' => 'Hôpital'];

    public function getAllAfiliations()
    {
        return self::afiliatons;
    }

    public function getAllLieux()
    {
        return self::lieux;
    }

    public function getDece(Dece $dece) {
        return [
            'id' => $dece->getId(),
            'firstname' => $dece->getFirstname(),
            'lastname' => $dece->getLastname(),
            'afiliation' => $dece->getAfiliation(),
            'afiliationLabel' => self::afiliatons[$dece->getAfiliation()] ?? "",
            'lieu' => $dece->getLieu(),
            'lieuLabel' => self::lieux[$dece->getLieu()] ?? "",
            'date' => $dece->getDate()->format('Y-m-d'),
            'adress' => $this->locationUI->getLocation($dece->getLocation()),
            'notifPf' => $dece->isNotifPf(),
            'phone' => $dece->getPhone(),
        ];
    }


}