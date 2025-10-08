<?php
namespace App\Services\Chat;

use App\Entity\ChatParticipant;
use App\Entity\ChatThread;
use App\Entity\Coaching\Coach;
use App\Entity\Coaching\CoachAthlete;
use App\Entity\Coaching\Customer;
use App\Entity\Coaching\ShareCalendar;
use App\Entity\Coaching\ShareCalendarAccess;
use App\Entity\Coaching\ShareCalendarManager;
use App\Entity\Media;
use App\Entity\Relation;
use App\Entity\SocialFriend;
use App\Entity\User;
use App\Services\Coaching\CoachAthleteSecurityService;
use App\Services\S3Service;
use App\Services\UploadMediaService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ThreadService {

    CONST S3BUCKET = 'muslimconect-images';

    public function __construct(EntityManagerInterface $entityManager,
                                S3Service $s3Service, UploadMediaService $uploadMediaService) {
        $this->entityManager = $entityManager;
        $this->s3Service = $s3Service;
        $this->uploadMediaService = $uploadMediaService;
    }


    public function getSimpleThreadFoUsers($currentUser, $user2) {
        /** @var ChatThread $thread */
        $thread = $this->entityManager->getRepository(ChatThread::class)->findThreadSimpleForUsers($currentUser, $user2);
        if(is_null($thread)) {
            $thread = new ChatThread();
            $thread->setType('simple');
            $thread->setCreatedBy($currentUser);
            //ajouter participant creator
            $mainParticipant = new ChatParticipant();
            $mainParticipant->setUser($currentUser);
            $this->addParticipantsOnThread($thread, [$currentUser, $user2]);
            $thread->setLastUpdate(new \DateTime('now'));
            $this->entityManager->persist($thread);
            $this->entityManager->flush();
        } elseif(!is_null($thread->getDeletedAt())) {
            $thread->setDeletedAt(null);
            $this->entityManager->persist($thread);
            $this->entityManager->flush();
        }
        return $thread;
    }

    public function iniThread($threadId, $currentUser, $threadType, $userToAdds) {
        $currentThread = null;
        if(!is_null($threadId)) {
            $currentThread = $this->entityManager->getRepository(ChatThread::class)->findThread($threadId);
            if(!is_null($currentThread)) {
                if($currentThread->getCreatedBy()->getId() != $currentUser->getId()) {
                    throw new \ErrorException();
                }
                /** @var ChatParticipant $participants */
                $participants = $currentThread->getParticipants();
                //ne pas ajouter de participants à ne discussion simple !
                if(count($participants) >= 2 && $currentThread->getType() == 'simple') {
                    throw new \ErrorException();
                }
            }
        }
        if(is_null($currentThread)) {
            $currentThread = new ChatThread();
            $currentThread->setType($threadType);
            $currentThread->setCreatedBy($currentUser);
            //ajouter participant creator
            $mainParticipant = new ChatParticipant();
            $mainParticipant->setUser($currentUser);
            $currentThread->addParticipant($mainParticipant);
            //checker si il n'y a pas déjà une converstation existante entre 2 participants !
            if($currentThread->getType() == 'simple') {
                $existingThread = $this->entityManager->getRepository(ChatThread::class)
                    ->findThreadSimpleForUsers($currentUser, $userToAdds[0]);
                if(!is_null($existingThread)) {
                    $currentThread = $existingThread;
                }
            }
            $currentThread->setLastUpdate(new \DateTime('now'));
        }
        return $currentThread;
    }

    public function getUserToAddAsParticpants($userToAddIds, $currentUser) {
        return $this->entityManager->getRepository(User::class)->findUsersById($userToAddIds);
    }

    public function addParticipantsOnThread(ChatThread $thread, $userToAdds) {
        $existingParticipants = $thread->getParticipants();
        $existingParticipantUserIds = [];
        foreach ($existingParticipants as $existingParticipant) {
            $existingParticipantUserIds[] = $existingParticipant->getUser()->getId();
        }
        foreach ($userToAdds as $userToAdd) {
            if(!in_array($userToAdd->getId(), $existingParticipantUserIds)) {
                $newParticipant = new ChatParticipant();
                $newParticipant->setUser($userToAdd);
                $thread->addParticipant($newParticipant);
                $existingParticipantUserIds[] = $userToAdd->getId();
            }
        }
        //ne pas ajouter de participants à ne discussion simple !
        if(count($thread->getParticipants()) > 2 && $thread->getType() == 'simple') {
            throw new \ErrorException();
        }
    }

    public function userCanChatOnThread(ChatThread $thread, $user, $throwDeny) {
        $participants = $thread->getParticipants();
        $userID = $user->getId();
        foreach ($participants as $participant) {
            if($participant->getUser()->getId() == $userID) {
                return true;
            }
        }
        if($throwDeny) {
            throw new AccessDeniedException();
        }
        return false;
    }

    public function userCanManageThread(ChatThread $thread, $user, $throwDeny) {
        $userID = $user->getId();
        $canManage = $thread->getCreatedBy()->getId() == $userID;
        if($throwDeny && !$canManage) {
            throw new AccessDeniedException();
        }
        return $canManage;
    }
    
    public function saveImageFromRequest(Request $request, ChatThread $chatThread) {
        $base64String = $request->get('imageBase64');
        if(!is_null($base64String) and $base64String != "") {
            $image = $chatThread->getImage();
            $bucket = self::S3BUCKET;
            $folder = 'thread';
            if(!is_null($image)) {
                $bucket = $image->getBucket();
                $folder = $image->getFolder();
            } else {
                $image = new Media();
                $image->setType('thread-profile');
                $image->setFolder($folder);
                $image->setBucket($bucket);
                $chatThread->setImage($image);
            }
            $orignialImage = $this->s3Service->getImageFromBase64($base64String);
            $optimizedImagePath = $this->s3Service->optimizeImageBeforeUpload($orignialImage, 80, 80, true);
            $fileName = $this->s3Service->saveJpegFromLocalTmpPath($bucket, $folder, $optimizedImagePath);
            unlink($optimizedImagePath);
            //si médi déjà existant supprimer
            if(!is_null($image->getFilename())) {
                $this->s3Service->deleteFileFromMedia($image);
            }
            if(!is_null($fileName)) {
                $image->setFilename($fileName);
                $image->setVersion(time());
            }
        }
    }
}