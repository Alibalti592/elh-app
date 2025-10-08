<?php
namespace App\Controller\Api;

use App\Entity\NotifToSend;
use App\Entity\PrayNotification;
use App\Entity\User;
use App\Services\CRUDService;
use App\Services\PrayTimesService;
use App\Services\UtilsService;
use App\UIBuilder\LocationUI;
use App\UIBuilder\UserUI;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Pharaonic\Hijri\HijriCarbon;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PriereController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly UtilsService $utilsService,
                                private readonly CRUDService $CRUDService, private readonly LocationUI $locationUI,
                                private readonly PrayTimesService $prayTimesService, private readonly UserUI $userUI) {}

    const prays = [
        ['key' => 'fajr', 'label' => 'Alfajr'],['key' => 'chorouq', 'label' => 'Chorouq'],['key' => 'dohr', 'label' => 'Duhur'],
        ['key' => 'asr', 'label' => 'Alasr'],['key' => 'maghreb', 'label' => 'Maghrib'],['key' => 'icha', 'label' => 'Alisha']
    ];

    #[Route('/load-prieres')]
    public function loadPrays(Request $request): Response
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $setLocation = $request->get('location');
        if(!is_null($setLocation) && $setLocation != "") {
            $this->userUI->setUserLocation($currentUser, json_decode($setLocation, true));
            //clear notifs ..
        }
        $userLocation = $currentUser->getLocation();
        $jsonResponse = new JsonResponse();
        if(is_null($userLocation)) {
            $jsonResponse->setData([
                'praytime' => null,
            ]);
            return $jsonResponse;
        }
        $today =  new \DateTime('now');
        $praytimesUI = $this->prayTimesService->getPrayTimesOfDay($currentUser);
        Carbon::mixin(HijriCarbon::class);
        $todayMuslim = Carbon::now();
        $todayMuslimString = $todayMuslim->toHijri()->format('d F Y');

        $praytimeUI = [
            'location' => $this->locationUI->getLocation($userLocation),
            'date' => $today->format('d/m/Y'),
            'dateMuslim' => $todayMuslimString,
            'prieres' => $praytimesUI
        ];

        //si on a passé heure last priere load tomorrow ...
        $lastTimestamprpay = null;
        foreach ($praytimesUI as $praytime) {
            if(isset($praytime['timestamp'])) {
                $lastTimestamprpay = $praytime['timestamp'];
            }
        }
        if(!is_null($lastTimestamprpay)) {
            $now = new \DateTime('now');
            if($lastTimestamprpay <= $now->getTimestamp()) {
                $tomorrow =  new \DateTime('tomorrow');
                $praytimesUI = $this->prayTimesService->getPrayTimesOfDay($currentUser, $tomorrow);
                $praytimeUI = [
                    'location' => $this->locationUI->getLocation($userLocation),
                    'date' =>   $today->format('d/m/Y'),
                    'dateMuslim' => $todayMuslimString,
                    'prieres' => $praytimesUI
                ];
            }
        }

        $jsonResponse->setData([
            'praytime' => $praytimeUI,
        ]);
        return $jsonResponse;
    }

    #[Route('/save-pray-notif', methods: ['POST'])]
    public function savePrayNotif(Request $request): Response {
        $prayKey = $request->get('prayKey');
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $prayNotification = $this->entityManager->getRepository(PrayNotification::class)->findOneBy([
            'user' => $currentUser
        ]);
        if(is_null($prayNotification)) {
            $prayNotification = new PrayNotification();
            $prayNotification->setUser($currentUser);
            $prayNotification->setPrays([]);
        }
        $prays = $prayNotification->getPrays();
        $sendNotif = true;
        if(in_array($prayKey, $prays)) {
            foreach ($prays as $index => $key) {
                if($key == $prayKey) {
                    unset($prays[$index]);
                }
            }
            $prays = array_values($prays);
            $sendNotif = false;
        } else {
            $prays[] = $prayKey;
        }
        //update pray Notif !!
        $notifToSend = $this->entityManager->getRepository(NotifToSend::class)->findOneBy([
            'user' => $currentUser,
            'type' => $prayKey
        ]);
        if($sendNotif) {
            $praytimesUI = $this->prayTimesService->getPrayTimesOfDay($currentUser);
            if(is_null($notifToSend)) {
                $prayName = '';
                $praytimeUI = null;
                $timestamp = null;
                foreach ($praytimesUI as $pray) {
                    if($pray['key'] == $prayKey) {
                        $prayName = $pray['label'];
                        $timestamp = $pray['timestamp'] - 60*15 ; //-15min
                        $praytimeUI = $pray;
                    }
                }
                if(!is_null($praytimeUI) && $timestamp > time()) {
                    $notifToSend = new NotifToSend();
                    $notifToSend->setForPrayFromUI($currentUser, $praytimeUI);
                    $this->entityManager->persist($notifToSend);
                    $this->entityManager->flush();
                }
            }
        } elseif(!is_null($notifToSend)) {
            $this->entityManager->remove($notifToSend);
            $this->entityManager->flush();
        }
        $prayNotification->setPrays($prays);
        $this->entityManager->persist($prayNotification);
        $this->entityManager->flush();
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([]);
        return $jsonResponse;
    }

}
