<?php
namespace App\Services;

use App\Entity\Media;

class UploadMediaService {

    CONST S3BUCKET = 'muslimconect-images';
    public function __construct(S3Service $s3Service) {
        $this->s3Service = $s3Service;
    }

    public function uploadDocument($existingMedia, $documentToUpload, $documentType, $folder, $options = []) {
        if($documentToUpload['fileString'] != null) {
            $media = is_null($existingMedia) ? new Media() : $existingMedia;
            $media->setType($documentType);
            $media->setLabel($documentToUpload['label'] ?? 'Document');
            $sizePrefixes = $media->getSizePrefixes();
            $newSizePrefixes = null;
            //OPTIONS
            $bucket = $options['bucket'] ?? self::S3BUCKET;
            $acl = $options['acl'] ?? 'private';
            $maxWidth = $options['maxWidth'] ?? 1200;
            $maxHeight = $options['maxHeight'] ?? 1500;
            $base64Datas = $documentToUpload['fileString'];
            $crop = $options['crop'] ?? true;
            //si pdf
            $mimeType = $this->s3Service->getMimeTypeFromBase64($base64Datas);
            if($mimeType == 'application/octet-stream' || $mimeType == 'application/gpx+xml'|| $mimeType == 'application/tcx+xml') {
                $basName = 'fichier';
                if(isset($documentToUpload['name'])) {
                    $basName = $documentToUpload['name'];
                } elseif ($mimeType == 'application/tcx+xml' ) {
                    $basName = 'fichier.tcx';
                } elseif ($mimeType == 'application/gpx+xml' ) {
                    $basName = 'fichier.gpx';
                }
                $fileName = $this->s3Service->saveBase64File($bucket, $folder, $base64Datas, $basName, $acl);
            } elseif($mimeType == 'application/pdf') {
                $fileName = $this->s3Service->saveBase64PDF($bucket, $folder, $base64Datas, $acl);
            } else {
                $orignialImage = $this->s3Service->getImageFromBase64($base64Datas);
                $optimizedImagePath = $this->s3Service->optimizeImageBeforeUpload($orignialImage, $maxWidth, $maxHeight, $crop); //destroy original
                $fileName = $this->s3Service->saveJpegFromLocalTmpPath($bucket, $folder, $optimizedImagePath, $acl);
                unlink($optimizedImagePath);
                if(isset($options['thumbs'])) {
                    $newSizePrefixes = [];
                    foreach ($options['thumbs'] as $thumbDatas) {
                        $orignialImage = $this->s3Service->getImageFromBase64($base64Datas);
                        $optimizedImagePath = $this->s3Service->optimizeImageBeforeUpload($orignialImage, $thumbDatas['maxWidth'], $thumbDatas['maxHeight'], true);
                        $thumbFileName = $this->s3Service->saveJpegFromLocalTmpPath($bucket, $folder, $optimizedImagePath, $acl, "t_");
                        $newSizePrefixes[$thumbDatas['name']] = $thumbFileName;
                        unlink($optimizedImagePath);
                    }
                }
            }
            //si médi déjà existant
            if(!is_null($existingMedia)) {
                //retirer le media existant !
                $this->s3Service->deleteFileFromMedia($media);
                if(!empty($sizePrefixes)) {
                    $fakeMedia = clone $media;
                    foreach ($sizePrefixes as $sizePrefixe) {
                        $fakeMedia->setFilename($sizePrefixe);
                        $this->s3Service->deleteFileFromMedia($fakeMedia);
                    }
                }
            }
            $media->setFilename($fileName);
            $media->setFolder($folder);
            $media->setBucket($bucket);
            $media->setSizePrefixes($newSizePrefixes);
            $media->setVersion(time());
            return $media;
        } elseif(isset($documentToUpload['name']) && !is_null($existingMedia)) { //update label
            $existingMedia->setLabel($documentToUpload['name']);
            return $existingMedia;
        }
        return false;
    }


    public function duplicateMedia(Media $baseMedia, $newParams = []) {
        $media = clone $baseMedia;
        $newBucket = $newParams['bucket'] ?? $baseMedia->getBucket();
        $newFolder = $newParams['folder'] ?? $baseMedia->getFolder();
        $authorisation = $newParams['authorisation'] ?? 'private';
        //copy file and change name
        $temp= explode('.', $baseMedia->getFilename());
        $extension= end($temp);
        $newFileName = $this->s3Service->randomFileName().'.'.$extension;
        $this->s3Service->copyMedia($baseMedia, $newFolder.'/'.$newFileName, $newBucket, $authorisation);
        $media->setFilename($newFileName);
        $media->setBucket($newBucket);
        $media->setFolder($newFolder);
        $media->setVersion(time());
        return $media;
    }
}