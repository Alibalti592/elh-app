<?php
namespace App\UIBuilder;

use App\Entity\Obligation;
use App\Entity\Testament;
use App\Entity\User;
use App\Services\UtilsService;
use App\Twig\UserThumbExtension;

class ObligationUI {

    public function __construct(private readonly UtilsService $utilsService, private readonly LocationUI $locationUI, ) {
    }


    public function getObligation(Obligation $obligation, $canEdit = true, $currentUSer = null, $currentUserOnView = null) {
        $conditonTypeDisplay = "Restitution en une fois";
        if($obligation->getConditonType() == 'multiple') {
            $conditonTypeDisplay = "Restitution en plusieurs fois";
        } elseif($obligation->getConditonType() == 'notdefined') {
            $conditonTypeDisplay = "Quand tu pourras";
        } elseif($obligation->getConditonType() == 'other') {
            $conditonTypeDisplay = "Autre";
        }
        $createdBy = $obligation->getCreatedBy() ;
        $createdByName = $createdBy->getFullname();
        $creancierFirstName = ucfirst($obligation->getFirstname());
        $creancierLastName = ucfirst($obligation->getLastname());
	$currency = $obligation->getCurrency();	
        $emprunteurName = "";
        $emprunteurNum = "";
        $preteurName = "";
        $preteurNum = "";
        $type = $obligation->getType();

        if($type == 'jed' || $type == 'onm') {
            if(!is_null($obligation->getRelatedTo())) {
                if($type == 'jed') {
                    $emprunteur = $obligation->getCreatedBy();
                    $preteur = $obligation->getRelatedTo();
                } else {
                    $emprunteur = $obligation->getRelatedTo();
                    $preteur = $obligation->getCreatedBy();
                }
                if(!is_null($emprunteur) && !is_null($preteur)) {
                    $emprunteurNum =  $emprunteur->getPhonePrefix().$emprunteur->getPhone();
                    $emprunteurName =  ucfirst($emprunteur->getFirstname()). ' '. ucfirst($emprunteur->getLastname());
                    $preteurName = ucfirst($preteur->getFirstname()). ' '. ucfirst($preteur->getLastname());
                    $preteurNum =  $preteur->getPhonePrefix().$preteur->getPhone();
                }
            } else {
                $emprunteur = null;
                $preteur = null;
                if($type == 'jed') {
                    $emprunteur = $obligation->getCreatedBy();
                } else {
                    $preteur = $obligation->getCreatedBy();
                }
                if(!is_null($emprunteur)) {
                    $emprunteurNum =  $emprunteur->getPhonePrefix().$emprunteur->getPhone();
                    $emprunteurName =  ucfirst($emprunteur->getFirstname()). ' '. ucfirst($emprunteur->getLastname());
                    $preteurName = ucfirst($obligation->getFirstname()). ' '. ucfirst($obligation->getLastname());
                    $preteurNum =  $obligation->getTel();
                }
                if(!is_null($preteur)) {
                    $preteurNum =  $preteur->getPhonePrefix().$preteur->getPhone();
                    $preteurName = ucfirst($preteur->getFirstname()). ' '. ucfirst($preteur->getLastname());
                    $emprunteurName = ucfirst($obligation->getFirstname()). ' '. ucfirst($obligation->getLastname());
                    $emprunteurNum =  $obligation->getTel();
                }
            }
            if(!is_null($currentUSer) && !is_null($obligation->getRelatedTo())) {
                //inverser type de dette si c'est le relatedto
                if($currentUSer->getId() == $obligation->getRelatedTo()->getId()) {
                    if($type == 'jed') {
                        $type = 'onm';
                    } elseif($type == 'onm') {
                        $type = 'jed';
                    }
                }
            }
        } else { //amana
            $emprunteur = $obligation->getCreatedBy();
            $emprunteurName = ucfirst($emprunteur->getFirstname()). ' '. ucfirst($emprunteur->getLastname());
            $emprunteurNum =  $emprunteur->getPhonePrefix().$emprunteur->getPhone();
            if(!is_null($obligation->getRelatedTo())) {
                $preteur = $obligation->getRelatedTo();
                $preteurName = ucfirst($preteur->getFirstname()). ' '. ucfirst($preteur->getLastname());
                $preteurNum =  $preteur->getPhonePrefix().$preteur->getPhone();
            }
        }

        $cardOtherName = $creancierFirstName. " ".$creancierLastName;
        $cardOtherTel = $obligation->getTel();
        //ssi on a un compte related !!
        if(!is_null($currentUserOnView) && !is_null($obligation->getRelatedTo())) {
            if($obligation->getRelatedTo()->getId() == $currentUserOnView->getId() && !is_null($obligation->getCreatedBy())) {
                /** @var User $otherUser */
                $otherUser = $obligation->getCreatedBy();
                $cardOtherName = ucfirst($otherUser->getFirstname()). " ".ucfirst($otherUser->getLastname());
                $cardOtherTel = $otherUser->getPhonePrefix(). " ".$otherUser->getPhone();
            }
        }
        if(!is_null($currentUserOnView) && !is_null($obligation->getCreatedBy())) {
            if($obligation->getCreatedBy()->getId() == $currentUserOnView->getId() && !is_null($obligation->getRelatedTo())) {
                /** @var User $otherUser */
                $otherUser = $obligation->getRelatedTo();
                $cardOtherName = ucfirst($otherUser->getFirstname()). " ".ucfirst($otherUser->getLastname());
                $cardOtherTel = $otherUser->getPhonePrefix(). " ".$otherUser->getPhone();
            }
        }

        $amount = $obligation->getAmount();
        return [
    'id' => $obligation->getId(),
    'type' => $type,
    'preteurName' => $preteurName,
    'emprunteurName' => $emprunteurName,
    'emprunteurNum' => $emprunteurNum,
    'preteurNum' => $preteurNum,
    'createdByName' => $createdByName,
    'firstname' => $creancierFirstName,
    'lastname' => $creancierLastName,
    'cardOtherName' => $cardOtherName,
    'cardOtherTel' => $cardOtherTel,
    'adress' => $obligation->getAdress(),
    'tel' => $obligation->getTel(),
    'amount' => floatval($amount),
        'remainingAmount' => floatval($obligation->getRemainingAmount() ?? $amount), // ✅ new field

    'date' => $obligation->getDate()?->format('Y-m-d'),
    'dateDisplay' => $obligation->getDate()?->format('d/m/Y'),
    'raison' => $obligation->getRaison(),
    'currency'=> $obligation->getCurrency(),
    'delay' => $obligation->getDelay(),
    'conditonType' => $obligation->getConditonType(),
    'conditonTypeDisplay' => $conditonTypeDisplay,
    'moyen' => $obligation->getMoyen(),
    'canEdit' => $canEdit,
    'status' => $obligation->getStatus(),
    'dateStart' => $obligation->getDateStart()?->format('Y-m-d'),
    'dateStartDisplay' => $obligation->getDateStart()?->format('d/m/Y'),
    'isRelatedToUser' => !is_null($obligation->getRelatedTo()),
    'relatedUserId' => $obligation->getRelatedTo()?->getId(),
        'fileUrl' => $obligation->getFileUrl(), // <-- add this line


];

 
    }

    public function getTestament(Testament $testament) {
        $from = $testament->getCreatedBy()->getFirstname()." ".$testament->getCreatedBy()->getLastname();
        return [
            'id' => $testament->getId(),
            'from' => $from,
            'location' => $testament->getLocation(),
            'family' => $testament->getFamily(),
            'goods' => $testament->getGoods(),
            'toilette' => $testament->getToilette(),
            'fixe' => $testament->getFixe(),
            'lastwill' => $testament->getLastwill(),
            'updateAt' => $testament->getUpdateAt()?->format('Y-m-d H:i:s'),
        ];
    }
}
