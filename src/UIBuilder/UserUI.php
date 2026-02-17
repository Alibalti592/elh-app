<?php
namespace App\UIBuilder;

use App\Entity\Coaching\CoachAthlete;
use App\Entity\Location;
use App\Entity\NotifToSend;
use App\Entity\PrayNotification;
use App\Entity\User;
use App\Services\PrayTimesService;
use App\Services\S3Service;
use App\Services\Social\ProfileService;
use App\Services\UtilsService;
use App\Twig\UserThumbExtension;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Lock\LockFactory;

class UserUI {


    public function __construct(private readonly EntityManagerInterface $entityManager,
                                private readonly CacheItemPoolInterface $cacheApp, private readonly S3Service $s3Service,
                                private readonly PrayTimesService $prayTimesService,
                                private readonly LockFactory $lockFactory) {

    }

    public function clearUserUI(User $user) {
        $cacheKey = "userprofilui-".$user->getId();
        $this->cacheApp->deleteItem($cacheKey);
    }

    public function getUserProfilUI(User $user) {
        $cacheKey = "userprofilui-".$user->getId();
        $cachedDatas = $this->cacheApp->getItem($cacheKey);
        //TODO REMOVE
        $this->cacheApp->deleteItem($cacheKey);
        if (!$cachedDatas->isHit()) {
            $cachedDatas->expiresAfter(3600);
            $userThumb = $this->getUserThumb($user);
            $city = "";
            if(!is_null($user->getLocation())) {
                $city = $user->getLocation()->getCity();
            }
            $photo = null;
            if(!is_null($user->getPhoto())) {
                $photo = $this->s3Service->getURLFromMedia($user->getPhoto());
            }
            $userUI = [
                'id' => $user->getId(),
                'firstname' => ucfirst($user->getFirstname()),
                'lastname' => ucfirst($user->getLastname()),
                'fullname' => ucfirst($user->getFirstname()).' '.ucfirst($user->getLastname()),
                'userLetters' => mb_strtoupper(mb_substr($user->getFirstname(),0,1).mb_substr($user->getLastname(),0,1)),
                'email' => $user->getEmail(),
                'phone' => $user->getPhone(),
                'phonePrefix' => $user->getPhonePrefix(),
                'phoneFull' => $user->getPhonePrefix().$user->getPhone(),
                'created' => $user->getCreateAt()->format('d/m/Y'),
                'thumb' => $userThumb,
                'city' => $city,
                'photo' => $photo,
                'status' => $user->getStatus(),
                'authProvider' => $user->getAuthProvider(),
            ];
            $cachedDatas->set(json_encode($userUI));
            $this->cacheApp->save($cachedDatas);
        } else {
            $userUI = json_decode($cachedDatas->get(), true);
        }
        return $userUI;
    }

    
    public function setUserLocation($currentUser, $locationArr)
    {
        if(isset($locationArr['label'])) {
            $location = $currentUser->getLocation();
            if(is_null($location)) {
                $location = new Location();
                $currentUser->setLocation($location);
            }
            $location->setLabel($locationArr['label']);
            if(isset($locationArr['adress'])) {
                $location->setAdress($locationArr['adress']);
            }
            $location->setCity($locationArr['city']);
            $location->setLat(floatval($locationArr['lat']));
            $location->setLng(floatval($locationArr['lng']));
            $location->setPostCode($locationArr['postcode']);
            $location->setRegion($locationArr['region']);
            $this->entityManager->persist($currentUser);
            $this->entityManager->persist($location);
            $this->entityManager->flush();
            $this->clearUserUI($currentUser);
            //clear notifs priere
            $this->updatePriereNotifs($currentUser);
        }
    }

    public function updatePriereNotifs($user) {
        $userLock = $this->lockFactory->createLock('pray_notif_user_'.$user->getId(), 30);
        if (!$userLock->acquire()) {
            return;
        }
        try {
            $prayNotification = $this->entityManager->getRepository(PrayNotification::class)->findOneBy([
                'user' => $user
            ]);
            if(!is_null($prayNotification)) {
                $this->entityManager->getRepository(NotifToSend::class)->deletePrayNotifOfUser($user);
                //add new
                $praytimesUI = $this->prayTimesService->getPrayTimesOfDay($prayNotification->getUser());
                foreach ($praytimesUI as $praytimeUI) {
                    if($praytimeUI['isNotified']) {
                        $sendTimestamp = ((int) $praytimeUI['timestamp']) - (60 * 15);
                        if ($sendTimestamp > time()) {
                            $notifToSend = new NotifToSend();
                            $notifToSend->setForPrayFromUI($user, $praytimeUI);
                            $this->entityManager->persist($notifToSend);
                        }
                    }
                }
                $this->entityManager->flush();
            }
        } finally {
            $userLock->release();
        }
    }

    public function getUserThumb($user)
    {
        return "";
//        $userProfile = $user->getProfile();
//        $filename = str_replace( '.png', '.jpeg', $userProfile->getPhoto()); //v1
//        if(strlen($filename) > 3) {
//            return self::CLIENTTHUMBS3URL.$filename;
//        }
//        return "";
    }

}
