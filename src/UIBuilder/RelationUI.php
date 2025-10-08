<?php
namespace App\UIBuilder;

use App\Entity\Relation;
use App\Entity\User;
use App\Twig\UserThumbExtension;

class RelationUI {

    public function __construct(private readonly UserUI $userUI) {

    }


    public function getRelation(Relation $relation, $relationUser, $activeUserIds = []) {
        return [
            'id' => $relation->getId(),
            'status' => $relation->getStatus(),
            'user' => $this->userUI->getUserProfilUI($relationUser),
            'active' => in_array($relationUser->getId(), $activeUserIds),
        ];
    }

    public function getRelationsListToValidate($relations) {
        $relationList = [];
        /** @var Relation $relation */
        foreach ($relations as $relation) {
            $relationUser = $relation->getUserSource();
            $relationList[] = $this->getRelation($relation, $relationUser);
        }
        return $relationList;
    }


    public function getRelationsList($relations, $currentUser, $activeUserIds = []) {
        $relationList = [];
        /** @var Relation $relation */
        foreach ($relations as $relation) {
            $currentUserId = $currentUser->getId();
            if($currentUserId == $relation->getUserSource()->getId()) {
                $relationUser = $relation->getUserTarget();
            } else {
                $relationUser = $relation->getUserSource();
            }
            $relationList[] = $this->getRelation($relation, $relationUser, $activeUserIds);
        }
        return $relationList;
    }

    //Relations
    public function getSearchedRelationsList($users, $currentUser, $relations) {
        $searchRelationsList = [];
        /** @var User $user */
        foreach ($users as $user) {
            if($currentUser->getId() != $user->getId()) {
                $existingRelation = new Relation();
                $existingRelation->setStatus('none');
                /** @var SocialRelation $relation */
                foreach ($relations as $relation) {
                    if($relation->getUserSource()->getId() == $user->getId() || $relation->getUserTarget()->getId() == $user->getId()) {
                        $existingRelation = $relation;
                        break;
                    }
                }
                $searchRelationsList[] = $this->getRelation($existingRelation, $user);
            }
        }
        return $searchRelationsList;
    }

}