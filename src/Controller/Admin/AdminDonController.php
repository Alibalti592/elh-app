<?php

namespace App\Controller\Admin;

use App\Entity\Location;
use App\Entity\Don;
use App\Entity\Media;
use App\Services\CRUDService;
use App\Services\S3Service;
use App\Services\UtilsService;
use App\UIBuilder\DonUI;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminDonController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly UtilsService $utilsService,
                                private readonly CRUDService $CRUDService, private readonly DonUI $donUI, private readonly S3Service $s3Service) {}


    #[Route('/v-load-list-dons')]
    public function loadList(Request $request): Response
    {
        $crudParams = $this->CRUDService->getListParametersFromRequest($request);
        $dons = $this->entityManager->getRepository(Don::class)->findListFiltered($crudParams);
        $count = $this->entityManager->getRepository(Don::class)->countListFiltered($crudParams);
        $donUIs = [];
        foreach ($dons as $don) {
            $donUIs[] = $this->donUI->getDon($don);
        }
        $newDon = new Don();
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'dons' => $donUIs,
            'donIni' => $this->donUI->getDon($newDon),
            'totalItems' => $count,
        ]);
        return $jsonResponse;
    }

    #[Route('/v-save-don', methods: ['POST'])]
    public function savedon(Request $request): Response
    {
        $donDatas = json_decode($request->get('don'), true);
        if(!is_null($donDatas['id'])) {
            $don = $this->entityManager->getRepository(Don::class)->findOneBy([
                'id' =>  $donDatas['id']
            ]);
            if(is_null($don)) {
                throw new \ErrorException("Don introuvable");
            }
        } else {
            $don = new Don();
        }
        $description = $this->utilsService->htmlEncodeBeforeSave($donDatas['description']);
        $don->setName($donDatas['name']);
        $don->setDescription($description);
        $don->setLink($donDatas['link']);
        $this->entityManager->persist($don);
        $this->entityManager->flush();
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData();
        return $jsonResponse;
    }

    #[Route('/v-delete-don', methods: ['POST'])]
    public function deletedon(Request $request): Response
    {
        $donDatas = json_decode($request->get('don'), true);
        if(!is_null($donDatas['id'])) {
            $don = $this->entityManager->getRepository(Don::class)->findOneBy([
                'id' =>  $donDatas['id']
            ]);
            if(is_null($don)) {
                throw new \ErrorException("Don introuvable");
            }
            $this->entityManager->remove($don);
            $this->entityManager->flush();
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData();
        return $jsonResponse;
    }


    #[Route('/v-don-upload-photo', methods: ['POST'])]
    public function uploadDonlogo(Request $request) {
        $don = $this->entityManager->getRepository(Don::class)->findOneBy([
            'id' =>   $request->get('itemId')
        ]);
        if(is_null($don)) {
            throw new \ErrorException("Don introuvable");
        }
        $jsonResponse = new JsonResponse();
        $isUploaded = false;
        $base64String = $request->get('image');
        if(!is_null($base64String) and $base64String != "") {
            $encodedData = str_replace("data:image/jpeg;base64,", "",$base64String);
            $blob = base64_decode($encodedData);
            $image = $don->getImage();
            $filename = $don->getFileName();
            $folder = 'association';
            $bucket = $this->s3Service->getMainBucket();
            $type = 'image/jpeg';
            if(is_null($image)) {
                $image = new Media();
                $don->setImage($image);
            } else {
                $this->s3Service->deleteFileFromMedia($image);
            }
            $image->setFilename($filename);
            $image->setBucket($bucket);
            $image->setFolder($folder);
            $image->setType($type);
            $image->setVersion(time());
            $fullImgName = $folder.'/'.$filename;
            $this->s3Service->putObject($blob, 'Body', $fullImgName, $bucket, $type);
            $this->entityManager->persist($image);
            $this->entityManager->persist($don);
            $this->entityManager->flush();
            $isUploaded = true;
        }
        if(!$isUploaded) {
            $jsonResponse->setStatusCode(500);
        }
        $jsonResponse->setData(['don' => $this->donUI->getDon($don)]);

        return $jsonResponse;
    }
}
