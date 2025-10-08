<?php
namespace App\UIBuilder\Chat;

use App\Entity\ChatMessage;
use App\Entity\Media;
use App\Services\S3Service;
use App\Services\UnitsService;
use App\Services\UrlEncryptorService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemPoolInterface;

class MessageUI {

    CONST S3BUCKET = 'muslimconnect-private';
    CONST FOLDER = 'chat';

    public function __construct(EntityManagerInterface $entityManager, UrlEncryptorService $urlEncryptorService,
                                UnitsService $unitsService, private readonly S3Service $s3Service, private readonly CacheItemPoolInterface $cacheApp) {
        $this->entityManager = $entityManager;
        $this->urlEncryptorService = $urlEncryptorService;
        $this->unitsService = $unitsService;
    }

    public function getMessageUIs($chatMessages, $currentUserId, $threadId) {
        $messageUIs = [];
        foreach ($chatMessages as $chatMessage) {
            $messageUIs[] = $this->getMessageUI($chatMessage['message'], $chatMessage['userID'], $currentUserId, $threadId);
        }
        return $messageUIs;
    }

    public function getMessageUI(ChatMessage $chatMessage, $chatMessageUserId, $currentUserId, $threadId) {
        //cache base, add $userId outside cache
        $cacheKey = "chatmessage-".$chatMessage->getId();
        $cache = $this->cacheApp->getItem($cacheKey);
        if (!$cache->isHit()) {
            $showAuthor = true; //see later !
            $isDeleted = !is_null($chatMessage->getDeletedAt());
            $isEdited = !is_null($chatMessage->getUpdatedAt());
            $content = $chatMessage->getContent();
            if($isDeleted) {
                $content = "Message supprimé";
            }
            $type = 'text';
            $chatMessageUI = [
                'showAuthor' => $showAuthor,
                'id' => $this->urlEncryptorService->encrypt('msg-' . $chatMessage->getId()),
                'type' => $type,
                'edited' => $isEdited,
                'deleted' => $isDeleted,
                'data' => [
                    'text' => $content,
                    'meta' => "",
                ]
            ];
            $cache->set(json_encode($chatMessageUI));
            $this->cacheApp->save($cache);
        } else {
            $chatMessageUI = json_decode($cache->get(), true);
        }
        //extra data depending on loading
        $now = new \DateTime('now');
        if($chatMessage->getCreatedAt()->format('Y-m-d') == $now->format('Y-m-d')) {
            $meta = $this->unitsService->humanTimeDiff($chatMessage->getCreatedAt(), $now);
        } else {
            $meta = $chatMessage->getCreatedAt()->format('d/m/y');
        }
        $chatMessageUI["data"]['meta'] = $meta;
        $userId = "me";
        if($chatMessageUserId != $currentUserId) {
//            $userId = $this->urlEncryptorService->encrypt($chatMessageUserId + $threadId);
            $userId = $chatMessageUserId;
        }
        $chatMessageUI["author"] = $userId;
        //file 3h, cache it 3h ?
        $media = $chatMessage->getFile();
        $file = null;
        if(!is_null($media)) {
            $file = [
                'link' => $this->s3Service->getTemporaryFileLink($media, '+180 minutes'),
                'type' => $media->getType(),
                'label' => $media->getLabel(),
            ];
        }
        if(!is_null($file)) {
            $chatMessageUI['type'] = 'file';
        }
        $chatMessageUI['data']['file'] = $file;
        return $chatMessageUI;
    }

    public function getMessageId($encryptId) {
        $string = $this->urlEncryptorService->decrypt($encryptId);
        return explode('-', $string)[1];
    }

    public function deleteMessageCache(ChatMessage $chatMessage)
    {
        $cacheKey = "chatmessage-".$chatMessage->getId();
        $this->cacheApp->deleteItem($cacheKey);
    }

    public function deleteMessageFile(ChatMessage $chatMessage)
    {
        $media = $chatMessage->getFile();
        if(!is_null($media)) {
            $chatMessage->setFile(null);
            $this->s3Service->deleteFileFromMedia($media);
            $this->entityManager->persist($chatMessage);
            $this->entityManager->remove($media);
            $this->entityManager->flush();
        }
    }

    //for reverse set author show or not !!
    public function setShowAuthor($messageUIs) {
        $previousUserId = ""; //pur éviter afficher vignette et nom à chaque bulle
        foreach ($messageUIs as $messageUI) {
            $messageUserId = $messageUI['author'];
            $showAuthor = $messageUserId != $previousUserId;
            $messageUI['showAuthor'] = $showAuthor;
        }
        return $messageUIs;
    }


    public function uploadFile(ChatMessage $chatMessage, $filename, $base64File) {
        if(is_null($filename) || is_null($base64File)) {
            return false;
        }
        //check size
        $fileSize = $this->s3Service->getBase64FileSizeInMB($base64File);
        $media = new Media();
        $media->setLabel($filename);
        $chatMessage->setFile($media);
        //si pdf
        $mimeType = $this->s3Service->getMimeTypeFromBase64($base64File);
        $isImage = $this->s3Service->isImage($mimeType);
        if(!$isImage) {
            if($fileSize > 8) {
                return false;
            }
            $media->setType('chat-file');
            //extension $fileName
            $extension = null;
            $expl = explode('.', $filename);
            if(count($expl) >= 2) {
                $extension = '.'.strtolower(end($expl));
            }
            $fileName = $this->s3Service->saveBase64File(self::S3BUCKET, self::FOLDER, $base64File, $extension);
        } else { //image
            $media->setType('chat-image');
            $orignialImage = $this->s3Service->getImageFromBase64($base64File);
            if(in_array($mimeType, ['image/jpeg', 'image/png']) && $orignialImage) {
                $newImageTmpPath = $this->s3Service->optimizeImageBeforeUpload($orignialImage, 800, 1200, false);
                if($newImageTmpPath) { //get base64 back
                    $newBase64File = $this->s3Service->getBase64ImageFromTmpPath($newImageTmpPath);
                    $newFileSize = $this->s3Service->getBase64FileSizeInMB($newBase64File);
                    if($newFileSize < $fileSize) {
                        $fileSize = $newFileSize;
                        $base64File = $newBase64File;
                    }
                }
            } elseif ($orignialImage) {
                unlink($orignialImage);
            }
            if($fileSize > 8) {
                return false;
            }
            $fileName = $this->s3Service->saveJpegFromLocalTmpPath(self::S3BUCKET, self::FOLDER, $base64File, 'private');
        }
        $media->setFileSize($fileSize);
        $media->setFilename($fileName);
        $media->setFolder(self::FOLDER);
        $media->setBucket(self::S3BUCKET);
        $media->setVersion(time());
        $this->entityManager->persist($chatMessage);
        $this->entityManager->persist($media);
        $this->entityManager->flush();
        return true;
    }
}