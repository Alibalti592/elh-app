<?php

namespace App\Controller\Api;

use App\Entity\Dece;
use App\Entity\Imam;
use App\Entity\Location;
use App\Entity\Mosque;
use App\Entity\MosqueFavorite;
use App\Entity\MosqueNotifDece;
use App\Entity\NotifToSend;
use App\Entity\Pardon;
use App\Entity\Pompe;
use App\Entity\PompeNotification;
use App\Services\CRUDService;
use App\Services\NotificationService;
use App\Services\UtilsService;
use App\UIBuilder\DeceUI;
use App\UIBuilder\ImamUI;
use App\UIBuilder\PardonUI;
use App\UIBuilder\PompeUI;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DeceController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly UtilsService $utilsService,
                                private readonly CRUDService $CRUDService, private readonly DeceUI $deceUI,
                                private readonly PardonUI $pardonUI, private readonly ImamUI $imamUI, private readonly NotificationService $notificationService) {}

    #[Route('/load-deces')]
    public function loadDeceList(Request $request): Response
    {
        $currentUser = $this->getUser();
        $deces = $this->entityManager->getRepository(Dece::class)->getDecesOfuser($currentUser);
        $deceUIs = [];
        foreach ($deces as $dece) {
            $deceUIs[] = $this->deceUI->getDece($dece);
        }
        $pardons = $this->entityManager->getRepository(Pardon::class)->getPardonsOfuser($currentUser);
        $sharedPardons = $this->entityManager->getRepository(Pardon::class)->getPardonsSharedWith($currentUser);
        $pardonUIs = [];
        $sharedPardonUIs = [];
        foreach ($pardons as $pardon) {
            $pardonUIs[] = $this->pardonUI->getPardon($pardon, $currentUser);
        }
        foreach ($sharedPardons as $pardon) {
            $sharedPardonUIs[] = $this->pardonUI->getPardon($pardon, $currentUser);
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'deces' => $deceUIs,
            'pardons' => $pardonUIs,
            'sharedPardons' => $sharedPardonUIs,
        ]);
        return $jsonResponse;
    }

    #[Route('/load-deces-add-settings')]
    public function loadAddDeceSettings(Request $request): Response
    {
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'options' => $this->deceUI->getAllAfiliations(),
            'lieux' => $this->deceUI->getAllLieux()
        ]);
        return $jsonResponse;
    }

    #[Route('/save-dece', methods: ['POST'])]
    public function savePompe(Request $request): Response
    {
        $deceDatas = json_decode($request->get('dece'), true);
        $currentUser = $this->getUser();
        $dece = $this->entityManager->getRepository(Dece::class)->findOneBy([
            'id' => $deceDatas['id'],
            'createdBy' => $currentUser
        ]);
        $notifyPf = false;
        $notifyPfMobile = false;
        if(isset($deceDatas['notifPf'])) {
            $notifyPfMobile = boolval($deceDatas['notifPf']);
        }
        $isNewDece = false;
        if(is_null($dece)) {
            $dece = new Dece();
            $dece->setCreatedBy($currentUser);
            $notifyPf = $notifyPfMobile;
            $isNewDece = true;
        } else {
            if(!$dece->isNotifPf() && $notifyPfMobile) {
                $notifyPf = true;
            }
        }
        //location
        $location = $dece->getLocation();
        if(is_null($location)) {
            $location = new Location();
            $dece->setLocation($location);
        }
        $date = new \DateTime($deceDatas['date']);
        $dece->setFromUI($deceDatas);
        $dece->setDate($date);
        $location->setFromUI($deceDatas['adress']);
        $this->entityManager->persist($dece);
        $this->entityManager->persist($currentUser);
        $this->entityManager->flush();
        if($notifyPf) {
            $location = $dece->getLocation();
            if(!is_null($location)) {
                //pf to notify
                $pompes = $this->entityManager->getRepository(Pompe::class)
                    ->findPompesByDistance($location->getLat(), $location->getLng(), 30, true);
                $pompeIds = [];
                /** $pompe => ['pompe'], ['distance'] */
                foreach ($pompes as $pompeDatas) {
                    $pompeIds[] = $pompeDatas['pompe']->getId();
                }
                $pompeNotifExists = $this->entityManager->getRepository(PompeNotification::class)
                    ->finExistingPompeNotifications($pompeIds, $dece);
                $pompeExistIds = [];
                foreach ($pompeNotifExists as $pompeNotifExist) {
                    $pompeExistIds[] = $pompeNotifExist->getPompe()->getId();
                }
                //add if neede
                foreach ($pompes as $pompeDatas) {
                    $pompe = $pompeDatas['pompe'];
                    if(!in_array($pompe->getId(), $pompeExistIds)) {
                        $pompeNotif = new PompeNotification();
                        $pompeNotif->setPompe($pompe);
                        $pompeNotif->setDece($dece);
                        $this->entityManager->persist($pompeNotif);
                        //nofi pompe
                        $this->notificationService->notifPompeFunebreNewDece($pompeNotif);
                    }
                }
                $this->entityManager->flush();
            }
        }
        $notifyMosque = boolval($deceDatas['notifyMosque']);
        $notifyMosque = false; //pour l'instant pas utile
        if($notifyMosque) {
            $location = $dece->getLocation();
            if(!is_null($location)) {
                //notif mosque
                $mosques = $this->entityManager->getRepository(Mosque::class)
                    ->findMosquesByDistance($location->getLat(), $location->getLng(), 30, true);
                $mosqueIds = [];
                /** $pompe => ['pompe'], ['distance'] */
                foreach ($mosques as $mosqueDatas) {
                    $mosqueIds[] = $mosqueDatas['mosque']->getId();
                }
                $mosqueNotifExists = $this->entityManager->getRepository(MosqueNotifDece::class)
                    ->finExistingMosqueNotifications($mosqueIds, $dece);
                $mosqueExistIds = [];
                /** @var MosqueNotifDece $mosqueNotifExist */
                foreach ($mosqueNotifExists as $mosqueNotifExist) {
                    $mosqueExistIds[] = $mosqueNotifExist->getMosque()->getId();
                }
                //add if needed
                foreach ($mosques as $mosqueDatas) {
                    $mosque = $mosqueDatas['mosque'];
                    if(!in_array($mosque->getId(), $mosqueExistIds)) {
                        $mosqueNotif = new MosqueNotifDece();
                        $mosqueNotif->setMosque($mosque);
                        $mosqueNotif->setDece($dece);
                        $this->entityManager->persist($mosqueNotif);
                        $this->notificationService->notifMosqueNewDece($mosqueNotif);
                    }
                }
                $this->entityManager->flush();
            }
        }
        if($isNewDece) {
            $notifToSend = new NotifToSend();
            $notifToSend->setForDece($currentUser, $dece);
            $this->entityManager->persist($notifToSend);
            $this->entityManager->flush();
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'dece' => $this->deceUI->getDece($dece)
        ]);
        return $jsonResponse;
    }


    #[Route('/delete-dece', methods: ['POST'])]
    public function deleteDece(Request $request): Response
    {
        $currentUser = $this->getUser();
        $dece = $this->entityManager->getRepository(Dece::class)->findOneBy([
            'id' => $request->get('deceId'),
            'createdBy' => $currentUser
        ]);
        if(!is_null($dece)) {
            $demands = $this->entityManager->getRepository(PompeNotification::class)->findDemandsForDece($dece);
            foreach ($demands as $demand) {
                $this->entityManager->remove($demand);
            }
            $this->entityManager->flush();
            $this->entityManager->remove($dece);
            $this->entityManager->flush();
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([]);
        return $jsonResponse;
    }

    #[Route('/dece-notify-pfs', methods: ['POST'])]
    public function notifyPfs(Request $request): Response
    {
        $currentUser = $this->getUser();
        $dece = $this->entityManager->getRepository(Dece::class)->findOneBy([
            'id' => $request->get('deceId'),
            'createdBy' => $currentUser
        ]);
        $notifyPf = $dece->isNotifPf();
        if(!$notifyPf) {
            $dece->setNotifPf(true);
            $this->entityManager->persist($dece);
            $this->entityManager->flush();
            $location = $dece->getLocation();
            if(!is_null($location)) {
                //pf to notify
                $pompes = $this->entityManager->getRepository(Pompe::class)
                    ->findPompesByDistance($location->getLat(), $location->getLng(), 30, true);
                $pompeIds = [];
                /** $pompe => ['pompe'], ['distance'] */
                foreach ($pompes as $pompeDatas) {
                    $pompeIds[] = $pompeDatas['pompe']->getId();
                }
                $pompeNotifExists = $this->entityManager->getRepository(PompeNotification::class)
                    ->finExistingPompeNotifications($pompeIds, $dece);
                $pompeExistIds = [];
                foreach ($pompeNotifExists as $pompeNotifExist) {
                    $pompeExistIds[] = $pompeNotifExist->getPompe()->getId();
                }
                //add if neede
                foreach ($pompes as $pompeDatas) {
                    $pompe = $pompeDatas['pompe'];
                    if(!in_array($pompe->getId(), $pompeExistIds)) {
                        $pompeNotif = new PompeNotification();
                        $pompeNotif->setPompe($pompe);
                        $pompeNotif->setDece($dece);
                        $this->entityManager->persist($pompeNotif);
                        //nofi pompe
                        $this->notificationService->notifPompeFunebreNewDece($pompeNotif);
                    }
                }
                $this->entityManager->flush();
            }
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData();
        return $jsonResponse;
    }

    #[Route('/load-pompe-accept-demand')]
    public function pompeAcceptDemand(Request $request, PompeUI $pompeUI): Response
    {
        $currentUser = $this->getUser();
        $dece = $this->entityManager->getRepository(Dece::class)->findOneBy([
            'id' => $request->get('deceId'),
            'createdBy' => $currentUser
        ]);
        $demands = $this->entityManager->getRepository(PompeNotification::class)->findDemandsForDece($dece);
        $pompeNotifUIs = [];
        foreach ($demands as $demand) {
            $pompeNotifUIs[] = $pompeUI->getPompeDemand($demand, true);
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'demands' => $pompeNotifUIs,
        ]);
        return $jsonResponse;
    }

    #[Route('/load-imams')]
    public function loadImams(Request $request): Response
    {
        $currentUser = $this->getUser();
        $location = json_decode($request->get('location'), true);
        $defaultDistance = intval($request->get('distance')); //en km
        $imams = $this->entityManager->getRepository(Imam::class)
            ->findImamsByDistance($location['lat'], $location['lng'], $defaultDistance);
        $imamUIs = [];
        foreach ($imams as $imam) {
            $imamUIs[] = $this->imamUI->getImam($imam['imam'], $imam['distance']);
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'imams' => $imamUIs,
        ]);
        return $jsonResponse;
    }
}
