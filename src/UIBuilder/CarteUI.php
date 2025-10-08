<?php
namespace App\UIBuilder;

use App\Entity\Carte;
use App\Services\UtilsService;
use App\Twig\UserThumbExtension;

class CarteUI {

    public function __construct(private readonly UtilsService $utilsService, private readonly LocationUI $locationUI,
                                private readonly MosqueUI $mosqueUI, private readonly SalatUI $salatUI) {}

    const afiliatons = [
        'father' => 'Père',  'mother' => 'Mère', 'son' => 'Fils', 'dot' => 'Fille', 'cousin' => 'Cousin',
        'cousine' => 'Cousine', 'oncle' => 'Oncle', 'tante' => 'Tante', 'grandp' => 'Grand-Père', 'grandm' => 'Grand-Mère', 'bop' => 'Beau père', 'bom' => 'Belle mère', 'bof' => 'Beau frère'
        , 'bsis' => 'Belle sœur', 'brother' => 'Frère', 'sister' => 'Sœur', 'gt' => 'Grande tante',  'go' => 'Grand oncle'
    ];

    public function getAllAfiliations()
    {
        return self::afiliatons;
    }

    public function getCarte(Carte $carte, $currentUser = null) {
        $canEdit = false;
        if(!is_null($currentUser)) {
            $canEdit = $currentUser->getId() == $carte->getCreatedBy()->getId();
        }
        $date = new \DateTime();
        if(!is_null($carte->getDeathDate())) {
            $date = $carte->getDeathDate();
        }
        $sex = $this->getSexForCarte($carte);
        $title = 'En mon nom';
        if(($carte->getOnmyname() == 'toother' || $carte->getType() == 'death')) {
            $title = 'Au nom de : '.ucfirst($carte->getFirstname()). ' '.ucfirst($carte->getLastname());
        } else if($carte->getOnmyname() == 'myname' && !$canEdit && !is_null($carte->getCreatedBy())) {
            $title = 'Au nom de : '.ucfirst($carte->getCreatedBy()->getFirstname()). ' '.ucfirst($carte->getCreatedBy()->getLastname());
        }

        $salat = null;
        if(!is_null($carte->getSalat())) {
            $salat = $this->salatUI->getSalat($carte->getSalat());
        }
        return [
            'id' => $carte->getId(),
            'type' => $carte->getType(),
            'title' => $title,
            'onmyname' => $carte->getOnmyname(),
            'phone' => $carte->getPhone(),
            'phonePrefix' => $carte->getPhonePrefix(),
            'typeLabel' => $this->getTypeLabel($carte->getType()),
            'sex' => $sex,
            'locationName' => $carte->getLocationName(),
            'firstname' => ucfirst($carte->getFirstname()),
            'lastname' => ucfirst($carte->getLastname()),
            'afiliation' => $carte->getAfiliation(),
            'afiliationLabel' => self::afiliatons[$carte->getAfiliation()] ?? "",
            'date' => $date->format('Y-m-d'),
            'content' => $this->utilsService->htmlDecode($carte->getContent()),
            'canEdit' => $canEdit,
            'salat' => $salat
        ];
    }

    public function  getSexForCarte($carte) {
        $sex = 'm';
        if(in_array($carte->getAfiliation(), ['mother', 'dot', 'cousine', 'tante', 'grandm', 'bom', 'bsis', 'sister', 'sis', 'gt'])) {
            $sex = 'f';
        }
        return $sex;
    }

    public function getTypeLabel($type)
    {
        if($type == 'pardon') {
            return "Demande de pardon";
        } elseif($type == 'invocation') {
            return "Demande d'invocation";
        } elseif($type == 'remercie') {
            return "Remerciements";
        } elseif($type == 'searchdette') {
            return "Recherche de dettes";
        } elseif($type == 'salat') {
            return "Salât al-Janaza";
        }
        return "Annonce d'un décès";
    }

    public function getCartContentClean($content, Carte $carte) {
        if($carte->getOnmyname() == 'toother') {
            $otherName = ucfirst($carte->getFirstname()). ' '.ucfirst($carte->getLastname());
            $content = str_replace("{other_fullname}", $otherName, $content);
        } elseif($carte->getOnmyname() == 'myname') {
            $userName = $carte->getCreatedBy()->getFullname();
            $content = str_replace("{user_fullname}", $userName, $content);
        }
        $content = str_replace("{genre_affiliation}", $this->getAffiliationGenre($carte), $content);
        $afil = self::afiliatons[$carte->getAfiliation()] ?? "";
        $content = str_replace("{affiliation}", $afil, $content);
        $content = str_replace("{allyramou_genre}", $this->getAlRhamou($carte), $content);

        if($carte->getType() == 'searchdette') {
            $phone = $carte->getPhonePrefix()." ".$carte->getPhone();
            $content = str_replace("{other_phone}", $phone, $content);
        }
        return $content;
    }

    public function getAffiliationGenre($carte)
    {
        $sex = $this->getSexForCarte($carte);
        if($sex == 'f') {
            if($carte->getOnmyname() == 'toother') {
                return 'sa';
            }
            return 'ma';
        }
        if($carte->getOnmyname() == 'toother') {
            return 'son';
        }
        return 'mon';
    }

    public function getAlRhamou($carte)
    {
        $sex = $this->getSexForCarte($carte);
        if($sex == 'f') {
            return "allah y rhamaha";
        }
        return 'allah y rhamo';
    }
}