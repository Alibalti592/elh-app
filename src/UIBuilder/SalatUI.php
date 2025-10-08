<?php
namespace App\UIBuilder;

use App\Entity\Salat;
use App\Services\UtilsService;
use App\Twig\UserThumbExtension;

class SalatUI {

    public function __construct(private readonly UtilsService $utilsService, private readonly LocationUI $locationUI, private readonly MosqueUI $mosqueUI) {}

    const afiliatons = [
        'father' => 'Père',  'mother' => 'Mère', 'son' => 'Fils', 'dot' => 'Fille', 'cousin' => 'Cousin',
        'cousine' => 'Cousine', 'oncle' => 'Oncle', 'tante' => 'Tante', 'grandp' => 'Grand-Père', 'grandm' => 'Grand-Mère', 'bop' => 'Beau père', 'bom' => 'Belle mère', 'bof' => 'Beau frère'
        , 'bsis' => 'Belle sœur', 'brother' => 'Frère', 'sister' => 'Sœur', 'gt' => 'Grande tante',  'go' => 'Grand oncle'
    ];

    public function getAllAfiliations()
    {
        return self::afiliatons;
    }

    public function getSalat(Salat $salat, $currentUser = null) {

        $mosqueUI = null;
        $mosque = $salat->getMosque();
        $canEdit = false;
        if(!is_null($currentUser)) {
            $canEdit = $currentUser->getId() == $salat->getCreatedBy()->getId();
        }
        if(!is_null($mosque)) {
            $mosqueUI = $this->mosqueUI->getMosque($mosque);
        }

        $time = $salat->getCeremonyAt()->format('H').'h'.$salat->getCeremonyAt()->format('i');
        return [
            'id' => $salat->getId(),
            'firstname' => ucfirst($salat->getFirstname()),
            'lastname' => ucfirst($salat->getLastname()),
            'afiliation' => $salat->getAfiliation(),
            'afiliationLabel' => self::afiliatons[$salat->getAfiliation()] ?? "",
            'date' => $salat->getCeremonyAt()->format('Y-m-d'),
            'timeDisplay' => $time,
            'dateDisplayFull' => $salat->getCeremonyAt()->format('d/m/Y').' à '.$time,
            'adress' => !is_null($salat->getLocation()) ? $this->locationUI->getLocation($salat->getLocation()) : null,
            'content' => $this->utilsService->htmlDecode($salat->getContent()),
            'mosque' => $mosqueUI,
            'mosqueName' => is_null($salat->getMosqueName()) ? "" : $salat->getMosqueName(),
            'canEdit' => $canEdit,
            'cimetary' => $salat->getCimetary(),
        ];
    }

    public function getSalatForAdmin(Salat $salat) {
        $salatUI = $this->getSalat($salat);
        $salatUI['createdBy'] = $salat->getCreatedBy()->getFullname();
        $salatUI['createdById'] = $salat->getCreatedBy()->getId();
        $salatUI['dateDisplayFull'] = $salat->getCeremonyAt()->format('d/m/Y').' à '. $salatUI['timeDisplay'];
        $salatUI['dateDisplayFull'] = $salat->getCeremonyAt()->format('d/m/Y').' à '. $salatUI['timeDisplay'];
        $salatUI['canEdit'] = true;
        $mosqueManual = is_null($salat->getMosque()) ? $salat->getMosqueName() : null;
        $salatUI['mosqueManual'] = $mosqueManual;
        return $salatUI;
    }


}