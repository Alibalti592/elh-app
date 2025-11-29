<?php
namespace App\Controller\Api;

use App\Entity\NotifToSend;
use App\Entity\Location;
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
        $jsonResponse = new JsonResponse();
        $currentUser = $this->getUser();
        if(!$currentUser instanceof User) {
            $currentUser = null;
        }

        $locationData = $this->extractLocationData($request);

        $userLocation = null;
        if($currentUser instanceof User) {
            if(!is_null($locationData)) {
                $this->userUI->setUserLocation($currentUser, $locationData);
            }
            $userLocation = $currentUser->getLocation();
        } elseif(!is_null($locationData)) {
            $userLocation = $this->buildLocationFromArray($locationData);
        }

        if(is_null($userLocation)) {
            $jsonResponse->setData([
                'praytime' => null,
            ]);
            return $jsonResponse;
        }
        $today =  new \DateTime('now');
        if($currentUser instanceof User) {
            $praytimesUI = $this->prayTimesService->getPrayTimesOfDay($currentUser);
        } else {
            $praytimesUI = $this->prayTimesService->getPrayTimesOfDayForLocation($userLocation);
        }
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
                if($currentUser instanceof User) {
                    $praytimesUI = $this->prayTimesService->getPrayTimesOfDay($currentUser, $tomorrow);
                } else {
                    $praytimesUI = $this->prayTimesService->getPrayTimesOfDayForLocation($userLocation, $tomorrow);
                }
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
        $currentUser = $this->getUser();
        if(!$currentUser instanceof User) {
            return new JsonResponse([], Response::HTTP_UNAUTHORIZED);
        }
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

    private function extractLocationData(Request $request): ?array
    {
        $locationPayload = $request->get('location');
        if(!is_null($locationPayload) && $locationPayload !== "") {
            $decoded = json_decode($locationPayload, true);
            if(is_array($decoded)) {
                $normalized = $this->normalizeLocationData($decoded);
                if(!is_null($normalized)) {
                    return $normalized;
                }
            }
        }

        $lat = $request->get('lat');
        $lng = $request->get('lng');
        if(!is_null($lat) && !is_null($lng)) {
            $normalized = $this->normalizeLocationData([
                'lat' => $lat,
                'lng' => $lng,
                'city' => $request->get('city'),
                'region' => $request->get('region'),
                'country' => $request->get('country'),
                'postcode' => $request->get('postcode', $request->get('postCode')),
                'label' => $request->get('label'),
                'adress' => $request->get('adress', $request->get('address')),
            ]);
            if(!is_null($normalized)) {
                return $normalized;
            }
        }

        return null;
    }

    private function normalizeLocationData(array $locationArr): ?array
    {
        if(!isset($locationArr['lat'], $locationArr['lng'])) {
            return null;
        }
        if(!is_numeric($locationArr['lat']) || !is_numeric($locationArr['lng'])) {
            return null;
        }

        $city = $locationArr['city'] ?? '';
        $region = $locationArr['region'] ?? '';
        $country = $locationArr['country'] ?? '';
        $postcode = $locationArr['postcode'] ?? ($locationArr['postCode'] ?? '');
        $label = $locationArr['label'] ?? '';
        if($label === '') {
            $parts = array_filter([$city, $region !== '' ? $region : $country, $country], function ($value) {
                return !is_null($value) && $value !== '';
            });
            if(count($parts) > 0) {
                $label = implode(', ', array_unique($parts));
            } else {
                $label = trim((string)$locationArr['lat']).', '.trim((string)$locationArr['lng']);
            }
        }
        $adress = $locationArr['adress'] ?? ($locationArr['address'] ?? $label);

        return [
            'label' => $label,
            'adress' => $adress ?? '',
            'city' => $city ?? '',
            'region' => $region !== '' ? $region : ($country ?? ''),
            'postcode' => $postcode ?? '',
            'lat' => floatval($locationArr['lat']),
            'lng' => floatval($locationArr['lng']),
        ];
    }

    private function buildLocationFromArray(array $locationData): Location
    {
        $location = new Location();
        $location->setLabel($locationData['label']);
        $location->setAdress($locationData['adress']);
        $location->setCity($locationData['city']);
        $location->setRegion($locationData['region']);
        $location->setPostCode($locationData['postcode']);
        $location->setLat(floatval($locationData['lat']));
        $location->setLng(floatval($locationData['lng']));

        return $location;
    }

}
