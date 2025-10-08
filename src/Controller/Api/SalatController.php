<?php

namespace App\Controller\Api;

use App\Entity\Carte;
use App\Entity\CarteShare;
use App\Entity\Mosque;
use App\Entity\MosqueFavorite;
use App\Entity\Relation;
use App\Entity\Salat;
use App\Entity\Location;
use App\Entity\SalatShare;
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

class SalatController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly UtilsService $utilsService,
                                private readonly CRUDService $CRUDService, private readonly SalatUI $salatUI,
                                private readonly RelationUI $relationUI, private readonly NotificationService $notificationService, private readonly CarteUI $carteUI) {}

    #[Route('/load-salats')]
    public function loadSalatList(Request $request): Response
    {
        $currentUser = $this->getUser();
        $passedOnly = $request->get('passedOnly') == 'true';
        $salats = $this->entityManager->getRepository(Salat::class)->getSalatsOfuser($currentUser, $passedOnly);
        $salatUIs = [];
        $mySalatIds = [];
        foreach ($salats as $salat) {
            $mySalatIds[] = $salat->getId();
            $salatUIs[] = $this->salatUI->getSalat($salat, $currentUser);
        }

        $mosqueIds = $this->entityManager->getRepository(MosqueFavorite::class)->findMosqueFavoriteIds($currentUser);
        $salatsOfMosques = $this->entityManager->getRepository(Salat::class)->getSalatsInMosques($mosqueIds, $mySalatIds);
        $salatOfMosqueUIs = [];
        $salatOfMosqueIds = [];
        foreach ($salatsOfMosques as $salat) {
            $salatOfMosqueIds[] = $salat->getId();
            $salatOfMosqueUIs[] = $this->salatUI->getSalat($salat, $currentUser);
        }

        //salats shared
        $salatShares = $this->entityManager->getRepository(SalatShare::class)->getSalatsSharedOfuser($currentUser, $passedOnly);
        foreach ($salatShares as $salatShares) {
            if(!in_array($salatShares->getSalat()->getId(), $salatOfMosqueIds)) {
                $salatUI = $this->salatUI->getSalat($salatShares->getSalat(), $currentUser);
                $salatUI['canEdit'] = false;
                $salatOfMosqueUIs[] = $salatUI;
            }
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'salats' => $salatUIs,
            'salatsOfMosque' => $salatOfMosqueUIs,
        ]);
        return $jsonResponse;
    }

    #[Route('/load-next-salats-near')]
    public function loadSalatHome(Request $request): Response
    {
        $currentUser = $this->getUser();
        $allSalatUis = [];
        $salats = $this->entityManager->getRepository(Salat::class)->getSalatsOfUserComming($currentUser);
        $mySalatIds = [];
        foreach ($salats as $salat) {
            $allSalatUis[] = $this->salatUI->getSalat($salat, $currentUser);
            $mySalatIds[] = $salat->getId();
        }
        $mosqueIds = $this->entityManager->getRepository(MosqueFavorite::class)->findMosqueFavoriteIds($currentUser);
        //mosque close by location
        $location = $currentUser->getLocation();
        if(!is_null($location)) {
            //notif mosque
            $mosques = $this->entityManager->getRepository(Mosque::class)
                ->findMosquesByDistance($location->getLat(), $location->getLng(), 25, true);
            foreach ($mosques as $mosque) {
                $mosqueId = $mosque['mosque']->getId();
                if(!in_array($mosqueId, $mosqueIds)) {
                    $mosqueIds[] = $mosqueId;
                }
            }
        }
        $salatsOfMosques = $this->entityManager->getRepository(Salat::class)->getSalatsInMosques($mosqueIds, $mySalatIds);
        foreach ($salatsOfMosques as $salat) {
            $allSalatUis[] = $this->salatUI->getSalat($salat, $currentUser);
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'salats' => $allSalatUis,
        ]);
        return $jsonResponse;
    }



    #[Route('/load-salats-add-settings')]
    public function loadAddSalatSettings(Request $request): Response
    {
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'options' => $this->salatUI->getAllAfiliations(),
        ]);
        return $jsonResponse;
    }

    #[Route('/check-existing-salat', methods: ['POST'])]
    public function checkexistingSalat(Request $request): Response
    {
        $salatDatas = json_decode($request->get('salat'), true);
        $existeSalat = false;
        $salatUI = null;
        $confirmPhrase = "";
        if(is_null($salatDatas['id'])) {
            //check mosque date
            $ceremonyAt = new \DateTime($salatDatas['date']);
            $mosque = null;
            if(isset($salatDatas['mosque']['id'])) {
                $mosque = $this->entityManager->getRepository(Mosque::class)->findOneBy([
                    'id' => $salatDatas['mosque']['id']
                ]);
            }
            $existinggSalats = $this->entityManager->getRepository(Salat::class)->existingSalats($ceremonyAt, $mosque);
            /** @var Salat $existinggSalat */
            foreach ($existinggSalats as $existingSalat) {
                if(strtolower($existingSalat->getFirstname()) == strtolower($salatDatas['firstname']) ||
                    strtolower($existingSalat->getLastname()) == strtolower($salatDatas['lastname'])) {
                    $existeSalat = true;
                    $salatUI = $this->salatUI->getSalat($existingSalat);
                    $mosquePhrase = "";
                    if(!is_null($salatUI['mosque'])) {
                        $mosquePhrase = " à la mosqué : ". $salatUI['mosque']['name']. " ";
                    }
                    $confirmPhrase = "Une Salat al-janaza a déjà été ajouté  dans Muslim Connect pour : "
                        .$salatUI['firstname'] . " ". $salatUI['lastname']. " le ".$salatUI['dateDisplayFull'].$mosquePhrase.
                        ".\nAfin de ne pas créer plusieurs notifications et Salats al-janaza tu peux ajouter l'existante";
                }
            }
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'existSalat' => $existeSalat,
            'salat' => $salatUI,
            'confirmPhrase' => $confirmPhrase,
        ]);
        return $jsonResponse;
    }

    #[Route('/save-salat', methods: ['POST'])]
    public function saveSalat(Request $request): Response
    {
        $salatDatas = json_decode($request->get('salat'), true);
        $currentUser = $this->getUser();
        $salat = $this->entityManager->getRepository(Salat::class)->findOneBy([
            'id' => $salatDatas['id'],
            'createdBy' => $currentUser
        ]);
        if(is_null($salat)) {
            $salat = new Salat();
            $salat->setCreatedBy($currentUser);
            $salatCarte = new Carte();
            $salatCarte->setSalat($salat);
            $salatCarte->setCreatedBy($currentUser);
        } else {
            $salatCarte = $salat->getCarte();
            if(is_null($salatCarte)) { //bug v1
                $salatCarte = new Carte();
                $salatCarte->setSalat($salat);
                $salatCarte->setCreatedBy($currentUser);
            }
        }
        $salat->setFromUI($salatDatas);
        $salatCarte->setFromSalat($salat);
        if(isset($salatDatas['mosque']['id'])) {
            $mosque = $this->entityManager->getRepository(Mosque::class)->findOneBy([
                'id' => $salatDatas['mosque']['id']
            ]);
            if(!is_null($mosque)) {
                $salat->setMosque($mosque);
            }
        } elseif(isset($salatDatas['mosqueName']) && $salatDatas['mosqueName'] != "") {
            $salat->setMosqueName($salatDatas['mosqueName']);
        }
        $this->entityManager->persist($salatCarte);
        $this->entityManager->persist($salat);
        $this->entityManager->persist($currentUser);
        $this->entityManager->flush();
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'salat' => $this->salatUI->getSalat($salat, $currentUser),
            'carte' => $this->carteUI->getCarte($salatCarte, $currentUser)
        ]);
        return $jsonResponse;
    }

    #[Route('/share-salat-to-me', methods: ['POST'])]
    public function shareSalatToMe(Request $request): Response
    {
        $salatDatas = json_decode($request->get('salat'), true);
        $currentUser = $this->getUser();
        $salat = $this->entityManager->getRepository(Salat::class)->findOneBy([
            'id' => $salatDatas['id'],
        ]);
        if(is_null($salat)) {
           throw new \ErrorException("Salata not found");
        }
        //share to
        $shareExist = $this->entityManager->getRepository(SalatShare::class)->findOneBy([
            'salat' => $salat,
            'user' => $currentUser,
        ]);
        if(is_null($shareExist)) {
            $salatShare = new SalatShare();
            $salatShare->setSalat($salat);
            $salatShare->setUser($currentUser);
            $this->entityManager->persist($salatShare);
            //set new card or share existing one ??
            $carteExist = $this->entityManager->getRepository(Carte::class)->findOneBy([
                'salat' => $salat,
            ]);
            if(is_null($carteExist)) {
                $salatCarte = new Carte();
                $salatCarte->setSalat($salat);
                $salatCarte->setCreatedBy($currentUser);
                $salatCarte->setFromSalat($salat);
                $this->entityManager->persist($salatCarte);
            } else {
                $carteShare = $this->entityManager->getRepository(CarteShare::class)->findOneBy([
                    'user' => $currentUser,
                    'carte' => $carteExist,
                ]);
                if(is_null($carteShare)) {
                    $carteShare = new CarteShare();
                    $carteShare->setUser($currentUser);
                    $carteShare->setCarte($carteExist);
                    $this->entityManager->persist($carteShare);
                }
            }
            $this->entityManager->flush();
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'salat' => $this->salatUI->getSalat($salat, $currentUser),
        ]);
        return $jsonResponse;
    }

    #[Route('/load-contact-share-salat')]
    public function loadListContacts(Request $request): Response
    {
        $currentUser = $this->getUser();
        $relations = $this->entityManager->getRepository(Relation::class)->findListOfRelations($currentUser, ['active'], 150);
        $nbRelations = $this->entityManager->getRepository(Relation::class)->countActiverRelations($currentUser);
        $salat = $this->entityManager->getRepository(Salat::class)->findOneBy([
            'id' => $request->get('salatId'),
        ]);
        $shareUserIds = $this->entityManager->getRepository(SalatShare::class)->findShareUserIds($salat);
        $relationsUI = $this->relationUI->getRelationsList($relations, $currentUser, $shareUserIds);
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'relations' => $relationsUI,
            'nbRelations' => $nbRelations,
        ]);
        return $jsonResponse;
    }

    #[Route('/share-salat-to-contact')]
    public function sharetocontact(Request $request): Response
    {
        $salatDatas = json_decode($request->get('salat'), true);
        $currentUser = $this->getUser();
        $salat = $this->entityManager->getRepository(Salat::class)->findOneBy([
            'id' => $salatDatas['id'],
        ]);
        if(is_null($salat)) {
            throw new \ErrorException("Salata not found");
        }
        $userToShareTo = $this->entityManager->getRepository(User::class)->findOneBy([
            'id' => $request->get('toUserId')
        ]);
        //share to
        $shareExist = $this->entityManager->getRepository(SalatShare::class)->findOneBy([
            'salat' => $salat,
            'user' => $userToShareTo,
        ]);
        if(is_null($shareExist)) {
            $salatShare = new SalatShare();
            $salatShare->setSalat($salat);
            $salatShare->setUser($userToShareTo);
            $this->entityManager->persist($salatShare);
            $carte = $salat->getCarte();
            if(!is_null($carte)) {
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

            $this->entityManager->flush();
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData();
        return $jsonResponse;
    }


    #[Route('/delete-salat', methods: ['POST'])]
    public function deleteSalat(Request $request): Response
    {
        $currentUser = $this->getUser();
        $salat = $this->entityManager->getRepository(Salat::class)->findOneBy([
            'id' => $request->get('salatId'),
            'createdBy' => $currentUser
        ]);
        if(!is_null($salat)) {
            $this->entityManager->remove($salat);
            $this->entityManager->flush();
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData();
        return $jsonResponse;
    }
}
