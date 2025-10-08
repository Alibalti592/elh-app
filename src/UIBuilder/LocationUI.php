<?php
namespace App\UIBuilder;

use App\Entity\Location;

class LocationUI {

    public function getLocation(Location $location) {
        return [
            'id' => $location->getId(),
            'label' => $location->getLabel(),
            'city' => $location->getCity(),
            'region' => $location->getRegion(),
            'context' => $location->getRegion(),
            'postcode' => $location->getPostCode(),
            'adress' => $location->getAdress(),
            'lat' => $location->getLat(),
            'lng' => $location->getLng()
        ];
    }


}