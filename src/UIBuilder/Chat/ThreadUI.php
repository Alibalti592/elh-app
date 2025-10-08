<?php
namespace App\UIBuilder\Chat;

use App\Entity\ChatParticipant;
use App\Entity\ChatThread;
use App\Entity\User;
use App\Repository\ChatParticipantRepository;
use App\Services\S3Service;
use App\Services\UnitsService;
use App\Services\UrlEncryptorService;
use App\UIBuilder\UserUI;
use App\UIBuilder\Chat\MessageUI;

class ThreadUI {

    public function __construct(MessageUI $messageUI, S3Service $s3Service, UserUI  $userUI, UnitsService $unitsService,
                                UrlEncryptorService $urlEncryptorService, ChatParticipantRepository $chatParticipantRepository) {
        $this->messageUI = $messageUI;
        $this->s3Service = $s3Service;
        $this->userUI = $userUI;
        $this->unitsService = $unitsService;
        $this->urlEncryptorService = $urlEncryptorService;
        $this->chatParticipantRepository = $chatParticipantRepository;
    }

    public function getThreadUI(ChatThread $chatThread, $currentUserId, $threadIdsWithNotifications = []) {
        $image = "";
        $participants = $this->chatParticipantRepository->findParticipants($chatThread);
        $name = is_null($chatThread->getName()) ? "" : $chatThread->getName();
        $nbParticpants = "";
        if(!is_null($chatThread->getImage())) {
            $image = $this->s3Service->getURLFromMedia($chatThread->getImage());
        }
        if($chatThread->getType() == 'simple') {
            /** @var ChatParticipant $participant */
            foreach ($participants as $participant) {
                if($participant->getUser()->getId() != $currentUserId) {
                    $userProfile = $this->userUI->getUserProfilUI($participant->getUser());
                    $name = $userProfile["fullname"];
                    $image = $userProfile["photo"];
                    break;
                }
            }
        } else {
            if(strlen($name) == 0) {
                $nbToShow = 2;
                /** @var ChatParticipant $participant */
                foreach ($participants as $participant) {
                    if($nbToShow > 0) {
                        if($participant->getUser()->getId() != $currentUserId) {
                            $userProfile = $this->userUI->getUserProfilUI($participant->getUser());
                            $name = $name. ' '. $userProfile["fullname"];
                            $name =  $nbToShow == 2 ? $name.", " : $name."..." ;
                            $nbToShow--;
                        }
                    } else {
                        break;
                    }
                }
            }
            if(count($participants) == 1) {
                $nbParticpants =  '1 participant';
            } else {
                $nbParticpants =  count($participants).' participants';
            }
        }
        $name = strlen($name ) > 25 ? mb_substr($name,0, 25).'...' : $name;
        $type = $chatThread->getType();
        $hasMessage = in_array($chatThread->getId(), $threadIdsWithNotifications);
        return [
            'id' => $chatThread->getId(),
            'name' => $name,
            'groupName' => is_null($chatThread->getName()) ? "" : $chatThread->getName(),
            'image' => $image,
            'type' => $type,
            'nbParticpants' => $nbParticpants,
            "lastMessage" => $this->getShortMessage($chatThread),
            "lastUpdate" => $this->unitsService->humanTimeDiff($chatThread->getLastUpdate(), new \DateTime('now')),
            "administrator" => $currentUserId == $chatThread->getCreatedBy()->getId(),
            'hasMessage' => $hasMessage
        ];
    }

    public function getShortMessage(ChatThread $chatThread) {
        if(is_null($chatThread->getLastMessage())) {
            return "";
        } else {
            $maxLength = 40;
            $content = $chatThread->getLastMessage()->getContent();
            return strlen($content) > $maxLength ? mb_substr($content, 0 , $maxLength).'...' : $content;
        }
    }


    public function getAddUserList($users, $currentUserId, $currentParticipantsIds) {
        $list = [];
        /** @var User $user */
        foreach ($users as $user) {
            $userId = $user->getId();
            if($currentUserId != $userId) {
                $photo = null;
                if(!is_null($user->getPhoto())) {
                    $photo = $this->s3Service->getURLFromMedia($user->getPhoto());
                }
                $list[] = [
                    'id' => $userId,
                    'name' => $user->getFullname(),
                    'photo' => $photo,
                    'isParticipant' => in_array($userId, $currentParticipantsIds),
                ];
            }
        }
        return $list;
    }

    public function getThreadParticipants($participants, $threadId) {
        $participantUIs = [];
        foreach ($participants as $participant) {
            $userProfile = $this->userUI->getUserProfilUI($participant->getUser());
            $participantUIs[] = [
                'id' => $userProfile["id"],
                'name' => $userProfile["fullname"],
                'imageUrl' => $userProfile['photo'],
            ];
        }
        return $participantUIs;
    }

}