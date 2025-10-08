<?php
namespace App\UIBuilder;

use App\Entity\Don;
use App\Services\S3Service;
use App\Services\UtilsService;
use App\Twig\UserThumbExtension;

class DonUI {

    public function __construct(private readonly UtilsService $utilsService, private readonly S3Service $s3Service) {
    }


    public function getDon(Don $don) {
        $image = $don->getImage();
        $logo = null;
        if(!is_null($image)) {
            $logo = $this->s3Service->getURLFromMedia($image);
        }
        return [
            'id' => $don->getId(),
            'name' => is_null($don->getName()) ? "" : $don->getName(),
            'description' => $this->utilsService->htmlDecode($don->getDescription()),
            'link' => $don->getLink(),
            'logo'  => $logo
        ];
    }


}