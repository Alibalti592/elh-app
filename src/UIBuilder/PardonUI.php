<?php
namespace App\UIBuilder;

use App\Entity\Pardon;
use App\Entity\User;
use App\Services\UtilsService;

class PardonUI {

    public function __construct(private readonly UtilsService $utilsService) {}


    public function getPardon(Pardon $pardon, User $currentUser) {
        return [
            'id' => $pardon->getId(),
            'firstname' => ucfirst($pardon->getFirstname()),
            'lastname' => ucfirst($pardon->getLastname()),
            'content' => $pardon->getContent(),
            'canEdit' => $pardon->getCreatedBy()->getId() == $currentUser->getId()
        ];
    }


}