<?php

namespace App\Controller\Admin;

use App\Entity\Location;
use App\Entity\Mosque;
use App\Entity\User;
use App\Services\CRUDService;
use App\Services\UtilsService;
use App\UIBuilder\MosqueUI;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminMosqueController extends AbstractController
{
    private const GOOGLE_MAPS_API_KEY = 'AIzaSyC2t8GvZFa6Ld6fbKM6_m2n3M0JoOmI03w';

    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly UtilsService $utilsService,
                                private readonly CRUDService $CRUDService, private readonly MosqueUI $mosqueUI) {}

    #[Route('/admin/mosque', name: 'admin_mosque_list')]
    public function index(): Response
    {
        return $this->render('admin/modules/mosque/list.twig', [

        ]);
    }

    #[Route('/v-load-list-mosques')]
    public function loadList(Request $request): Response
    {
        $crudParams = $this->CRUDService->getListParametersFromRequest($request);
        $mosques = $this->entityManager->getRepository(Mosque::class)->findListFilteredAdmin($crudParams);
        $count = $this->entityManager->getRepository(Mosque::class)->countListFiltered($crudParams);
        $mosqueUIs = [];
        foreach ($mosques as $mosque) {
            $mosqueUIs[] = $this->mosqueUI->getMosque($mosque);
        }
        $newMosque = new Mosque();
        $newMosque->setLocation(new Location());
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'mosques' => $mosqueUIs,
            'mosqueIni' => $this->mosqueUI->getMosque($newMosque),
            'totalItems' => $count,
        ]);
        return $jsonResponse;
    }

    #[Route('/v-save-mosque', methods: ['POST'])]
    public function savemosque(Request $request): Response
    {
        //"id":null,"name":"Mosquee Lyton","online":true,"description":"<p>fdsq ff sq</p>","location":{"label":"Lyon","postcode":"69001","city":"Lyon","region":"69, Rhône, Auvergne-Rhône-Alpes","lat":45.758,"lng":4.835}
        try {
            $mosqueDatas = json_decode($request->get('mosque'), true);
            if(!is_null($mosqueDatas['id'])) {
                $mosque = $this->entityManager->getRepository(Mosque::class)->findOneBy([
                    'id' =>  $mosqueDatas['id']
                ]);
                if(is_null($mosque)) {
                    throw new \ErrorException("Mosque introuvable");
                }
            } else {
                $mosque = new Mosque();
            }

            $description = $this->utilsService->htmlEncodeBeforeSave($mosqueDatas['description']);
            $mosque->setName($mosqueDatas['name']);
            $mosque->setDescription($description);
            $online = $mosqueDatas['online'] === true;
            $mosque->setOnline($online);
            //location
            $location = $mosque->getLocation();
            if(is_null($location)) {
                $location = new Location();
                $mosque->setLocation($location);
            }

            $locationDatas = $this->resolveLocationFromAddress($mosqueDatas['location'] ?? []);
            $location->setFromUI($locationDatas);

            if(isset($mosqueDatas['managedBy'])) {
                $managedBy = $this->entityManager->getRepository(User::class)->findOneBy([
                    'id' => $mosqueDatas['managedBy']['id']
                ]);
                $mosque->setManagedBy($managedBy);
            }

            $this->entityManager->persist($mosque);
            $this->entityManager->flush();
            $jsonResponse = new JsonResponse();
            $jsonResponse->setData();
            return $jsonResponse;
        } catch (\Throwable $th) {
            $jsonResponse = new JsonResponse();
            $jsonResponse->setStatusCode(500);
            $jsonResponse->setData([
                'message' => $th->getMessage() !== '' ? $th->getMessage() : "Impossible de récupérer les coordonnées de cette adresse."
            ]);
            return $jsonResponse;
        }
    }

    private function resolveLocationFromAddress(array $locationData): array
    {
        $fullAddress = trim((string) ($locationData['label'] ?? ''));
        if ($fullAddress === '') {
            throw new \InvalidArgumentException("Merci de saisir l'adresse complète de la mosquée.");
        }

        $url = 'https://maps.googleapis.com/maps/api/geocode/json?' . http_build_query([
            'address' => $fullAddress,
            'key' => self::GOOGLE_MAPS_API_KEY,
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            curl_close($ch);
            throw new \RuntimeException("Impossible de récupérer les coordonnées de cette adresse.");
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode !== 200) {
            throw new \RuntimeException("Impossible de récupérer les coordonnées de cette adresse.");
        }

        $decoded = json_decode((string) $response, true);
        if (($decoded['status'] ?? '') !== 'OK' || empty($decoded['results'][0])) {
            throw new \RuntimeException("Adresse introuvable. Merci de vérifier la saisie.");
        }

        $result = $decoded['results'][0];
        $geometry = $result['geometry']['location'] ?? null;
        if (!is_array($geometry) || !isset($geometry['lat'], $geometry['lng'])) {
            throw new \RuntimeException("Cette adresse ne retourne pas de coordonnées GPS.");
        }

        $componentsByType = [];
        foreach (($result['address_components'] ?? []) as $component) {
            foreach (($component['types'] ?? []) as $type) {
                $componentsByType[$type] = $component;
            }
        }

        $streetNumber = $componentsByType['street_number']['long_name'] ?? '';
        $route = $componentsByType['route']['long_name'] ?? '';
        $streetAddress = trim($streetNumber . ' ' . $route);

        $city = $componentsByType['locality']['long_name']
            ?? $componentsByType['postal_town']['long_name']
            ?? $componentsByType['administrative_area_level_3']['long_name']
            ?? $componentsByType['administrative_area_level_2']['long_name']
            ?? '';

        $region = $componentsByType['administrative_area_level_1']['long_name']
            ?? $componentsByType['administrative_area_level_2']['long_name']
            ?? $componentsByType['country']['long_name']
            ?? '';

        $postcode = $componentsByType['postal_code']['long_name'] ?? '';
        $country = $componentsByType['country']['long_name'] ?? '';

        return [
            'label' => $result['formatted_address'] ?? $fullAddress,
            'adress' => $streetAddress !== '' ? $streetAddress : $fullAddress,
            'city' => $city !== '' ? $city : $fullAddress,
            'postcode' => $postcode,
            'region' => $region,
            'country' => $country,
            'lat' => (float) $geometry['lat'],
            'lng' => (float) $geometry['lng'],
        ];
    }
}
