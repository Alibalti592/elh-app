<?php

namespace App\Controller\Api;

use App\Entity\CarteShare;
use App\Entity\CarteText;
use App\Entity\Mosque;
use App\Entity\Carte;
use App\Entity\Relation;
use App\Entity\Salat;
use App\Entity\User;
use App\Services\CRUDService;
use App\Services\NotificationService;
use App\Services\UtilsService;
use App\UIBuilder\CarteUI;
use App\UIBuilder\RelationUI;
use App\UIBuilder\SalatUI;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CarteController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly UtilsService $utilsService,
                                private readonly CRUDService $CRUDService, private readonly CarteUI $carteUI,
                                private readonly NotificationService $notificationService, private readonly RelationUI $relationUI,
    private readonly SalatUI $salatUI) {}

    #[Route('/load-cartes')]
    public function loadCarteList(Request $request): Response
    {
        $filter = $request->get('filter');
        $currentUser = $this->getUser();
        $carteUIs = [];
        $carteSahredUIs = [];
        if($filter == "create" || $filter == "send") {
            $cartes = $this->entityManager->getRepository(Carte::class)->getCartesOfuser($currentUser, $filter);
            foreach ($cartes as $carte) {
                $carteUIs[] = $this->carteUI->getCarte($carte, $currentUser);
            }
        } else {
            //shared cartes
            $carteShares = $this->entityManager->getRepository(CarteShare::class)->findSharedCartes($currentUser);
            foreach ($carteShares as $carteShare) {
                $carteSahredUIs[] = $this->carteUI->getCarte($carteShare->getCarte(), $currentUser);
            }
        }

        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'cartes' => $carteUIs,
            'carteShares' => $carteSahredUIs,
        ]);
        return $jsonResponse;
    }

    #[Route('/load-cartes-add-settings')]
    public function loadAddCarteSettings(Request $request): Response
    {
        $jsonResponse = new JsonResponse();
        $type = $request->get('type');
        $textes = [];
        $carteTextes = $this->entityManager->getRepository(CarteText::class)->findBy(['type' => $type]);
        foreach ($carteTextes as $carteTexte) {
            $content = $carteTexte->getContent();
            if(!$carteTexte->isForOther()) {
                $userName = $this->getUser()->getFullname();
                $content = str_replace("{user_fullname}", $userName, $content);
            }
            $textes[] = [
                'type' => $carteTexte->getType(),
                'content' => $content,
                'forOther' => $carteTexte->isForOther(),
            ];
        }
        $jsonResponse->setData([
            'options' => $this->carteUI->getAllAfiliations(),
            'textes' => $textes
        ]);
        return $jsonResponse;
    }

    #[Route('/load-carte-text-content')]
    public function loadCarteTextContent(Request $request): Response
    {
        $jsonResponse = new JsonResponse();
        $carteId = $request->get('carte');
        $carte = $this->entityManager->getRepository(Carte::class)->findOneBy([
            'id' => $carteId
        ]);
        $toOther = $carte->getOnmyname() == 'toother';
        $carteTexte = $this->entityManager->getRepository(CarteText::class)
            ->findTextOfCard($toOther, $carte->getType());
        $content = null;
        if($carteTexte != null) {
            $content = $this->carteUI->getCartContentClean($carteTexte->getContent(), $carte);
        }
        $jsonResponse->setData([
            'mainText' => $content,
        ]);
        return $jsonResponse;
    }

    #[Route('/save-carte', methods: ['POST'])]
    public function saveCarte(Request $request): Response
    {
        $carteDatas = json_decode($request->get('carte'), true);
        $currentUser = $this->getUser();
        $carte = $this->entityManager->getRepository(Carte::class)->findOneBy([
            'id' => $carteDatas['id'],
            'createdBy' => $currentUser
        ]);
        if(is_null($carte)) {
            $carte = new Carte();
            $carte->setCreatedBy($currentUser);
        }

        $carte->setFromUI($carteDatas);
        if(isset($carteDatas['mosque']['id'])) {
            $mosque = $this->entityManager->getRepository(Mosque::class)->findOneBy([
                'id' => $carteDatas['mosque']['id']
            ]);
            if(!is_null($mosque)) {
                $carte->setMosque($mosque);
            }
        }
        $this->entityManager->persist($carte);
        $this->entityManager->persist($currentUser);
        $this->entityManager->flush();
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            "carte" => $this->carteUI->getCarte($carte, $currentUser),
        ]);
        return $jsonResponse;
    }

    #[Route('/share-carte-to-contact', methods: ['POST'])]
    public function shareCarteToContact(Request $request): Response
    {
        $carteDatas = json_decode($request->get('carte'), true);
        $currentUser = $this->getUser();
        $carte = $this->entityManager->getRepository(Carte::class)->findOneBy([
            'id' => $carteDatas['id']
        ]);
        //weh should chec if user has carte shared or is creator !
        if(is_null($carte)) {
            throw  new \ErrorException('Carte cnat be shared !');
        }
        $toUserId = intval($request->get('toUserId'));
        $userToShareTo = $this->entityManager->getRepository(User::class)->findOneBy([
            'id' => $toUserId
        ]);
        if(is_null($userToShareTo)) {
            throw  new \ErrorException('Aucun user trouvé $toUserId : '.$toUserId);
        }
        $relation = $this->entityManager->getRepository(Relation::class)->findRelation($currentUser, $userToShareTo);
        if(!is_null($relation)) {
            $carteShare = $this->entityManager->getRepository(CarteShare::class)->findOneBy([
                'carte' => $carte,
                'user' => $userToShareTo,
            ]);
            if(!is_null($carteShare)) {
                $this->entityManager->remove($carteShare);
                $this->entityManager->flush();
            } else {
                $carteShare = new CarteShare;
                $carteShare->setCarte($carte);
                $carteShare->setUser($userToShareTo);
                $this->entityManager->persist($carteShare);
                $this->entityManager->flush();
                //notifs !!
                $this->notificationService->notifForCarte($carteShare);
            }
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'message' => 'La carte a été partagée !'
        ]);
        return $jsonResponse;
    }

    //share all
    #[Route('/share-carte-contacts', methods: ['POST'])]
    public function shareCarte(Request $request): Response
    {
        $carteDatas = json_decode($request->get('carte'), true);
        $currentUser = $this->getUser();
        $carte = $this->entityManager->getRepository(Carte::class)->findOneBy([
            'id' => $carteDatas['id'],
            'createdBy' => $currentUser
        ]);
        if(is_null($carte)) {
            throw  new \ErrorException('Carte cnat be shared !');
        }
        $relationUserIds = $this->entityManager->getRepository(Relation::class)->findListOfActiveRelationsUserIds($currentUser);
        $existingShareUserIds = $this->entityManager->getRepository(CarteShare::class)->findShareUserIds($carte);
        foreach ($relationUserIds as $userId) {
            if($userId != $currentUser->getId() && !in_array($userId, $existingShareUserIds)) {
                $carteShare = new CarteShare;
                $carteShare->setCarte($carte);
                $userTo = $this->entityManager->getReference(User::class, $userId);
                $carteShare->setUser($userTo);
                $this->entityManager->persist($carteShare);
                $this->entityManager->flush();
                //notifs !!
                $this->notificationService->notifForCarte($carteShare);
            }
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'message' => 'La carte a été partagée à vos contacts'
        ]);
        return $jsonResponse;
    }


    #[Route('/load-contact-share-carte')]
    public function loadList(Request $request): Response
    {
        $currentUser = $this->getUser();
        $relations = $this->entityManager->getRepository(Relation::class)->findListOfRelations($currentUser, ['active'], 150);
        $nbRelations = $this->entityManager->getRepository(Relation::class)->countActiverRelations($currentUser);
        $carte = $this->entityManager->getRepository(Carte::class)->findOneBy([
            'id' => $request->get('carteId'),
            'createdBy' => $currentUser
        ]);
        $shareUserIds = $this->entityManager->getRepository(CarteShare::class)->findShareUserIds($carte);
        //add page if nbRelations > 150 ... bouton charger
        $relationsUI = $this->relationUI->getRelationsList($relations, $currentUser, $shareUserIds);
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'relations' => $relationsUI,
            'nbRelations' => $nbRelations,
        ]);
        return $jsonResponse;
    }

    #[Route('/delete-carte', methods: ['POST'])]
    public function deleteCarte(Request $request): Response
    {
        $carteDatas = json_decode($request->get('carte'), true);
        $currentUser = $this->getUser();
        $carte = $this->entityManager->getRepository(Carte::class)->findOneBy([
            'id' => $carteDatas['id'],
            'createdBy' => $currentUser
        ]);
        if(is_null($carte)) {
            throw  new \ErrorException('Carte cnat be deleted !');
        }
        $this->entityManager->getRepository(CarteShare::class)->removeShareds($carte);
        $this->entityManager->remove($carte);
        $this->entityManager->flush();
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'message' => 'La carte a été supprimée'
        ]);
        return $jsonResponse;
    }

    #[Route('/delete-share-carte', methods: ['POST'])]
    public function deleteShareCarte(Request $request): Response
    {
        $carteDatas = json_decode($request->get('carte'), true);
        $currentUser = $this->getUser();
        $carte = $this->entityManager->getRepository(Carte::class)->findOneBy([
            'id' => $carteDatas['id'],
        ]);
        $jsonResponse = new JsonResponse();
        if(!is_null($carte)) {
            $carteSahre = $this->entityManager->getRepository(CarteShare::class)->findOneBy([
                'carte' => $carte,
                'user' => $currentUser
            ]);
            $this->entityManager->remove($carteSahre);
            $this->entityManager->flush();
            $jsonResponse->setData([
                'message' => 'La carte a été supprimée'
            ]);
        }

        return $jsonResponse;
    }
}
