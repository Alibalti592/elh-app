<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Services\S3Service;
use App\Services\UrlEncryptorService;
use App\UIBuilder\UserUI;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class LayoutController extends AbstractController
{
    public function __construct(private readonly CacheItemPoolInterface $cacheApp, UserUI $userUI,
                                UrlEncryptorService $urlEncryptorService, private readonly S3Service $s3Service, private readonly EntityManagerInterface $entityManager)
    {
        $this->userUI = $userUI;
        $this->urlEncryptorService = $urlEncryptorService;
    }

    #[Route('/v-load-menu-left')]
    #[IsGranted('ROLE_ADMIN')]
    public function loadSidebarMenu(Request $request): Response
    {
        $menuName = 'sidear';
        $currentRouteEncrypt = $request->get('cr');
        $user = $this->getUser();
        $firstname = $user->getFirstname();
        $lastname = $user->getLastname();
        $photoUrl = null;
        if(!is_null($user->getPhoto())) {
            $photoUrl = $this->s3Service->getURLFromMedia($user->getPhoto());
        }
        $headCard = [
            'name' => $firstname,
            'letters' => mb_strtoupper(mb_substr($firstname,0,1).mb_substr($lastname,0,1)),
            'image' => $photoUrl
        ];
        if ($this->getParameter('kernel.environment') == 'dev') {
            $this->cacheApp->deleteItem('menu_sidear');
        }
        $cachedMenu = $this->cacheApp->getItem('menu_' . $menuName);
        if (!$cachedMenu->isHit()) {
            $menu = [
                "items" => [
//                    [
//                        'name' => "Dasboard",
//                        'icon' => "icon-home",
//                        'id' => 'dashboard',
//                        'hasSub' => true,
//                        'subItems' => [
//                            [
//                                'name' => "Dashboard",
//                                'icon' => "iconido-structure",
//                                'url' => $this->generateUrl("dashboard"),
//                                'r' => "dashboard",
//                            ],
//                        ]
//                    ],
                    [
                        'name' => "Utilisateurs",
                        'icon' => "icon-users",
                        'id' => 'users',
                        'hasSub' => true,
                        'subItems' => [
                            [
                                'name' => "Liste des utilisateurs",
                                'icon' => "iconido-individuel",
                                'url' => $this->generateUrl("admin_user_list"),
                                'r' => "admin_user_list",
                            ],
                            [
                                'name' => "Pompes funèbres",
                                'icon' => "iconido-octagon",
                                'url' => $this->generateUrl("admin_pompe_list"),
                                'r' => "admin_pompe_list",
                            ],
                        ]
                    ],
                    [
                        'name' => "Contenu",
                        'icon' => "icon-box",
                        'id' => 'content',
                        'hasSub' => true,
                        'subItems' => [
                            [
                                'name' => "Carte Textes",
                                'icon' => "icon-feather",
                                'url' => $this->generateUrl("admin_carte_list"),
                                'r' => "admin_carte_list",
                            ],
                            [
                                'name' => "Salats al-janaza",
                                'icon' => "icon-codepen",
                                'url' => $this->generateUrl("admin_salat_list"),
                                'r' => "admin_salat_list",
                            ],
                            [
                                'name' => "Mosquées",
                                'icon' => "icon-home",
                                'url' => $this->generateUrl("admin_mosque_list"),
                                'r' => "admin_mosque_list",
                            ],
                            [
                                'name' => "Imams",
                                'icon' => "icon-users",
                                'url' => $this->generateUrl("admin_imam_list"),
                                'r' => "admin_imam_list",
                            ],
                            [
                                'name' => "Maraudes",
                                'icon' => "icon-thumbs-up",
                                'url' => $this->generateUrl("admin_maraude_list"),
                                'r' => "admin_maraude_list",
                            ],
                            [
                                'name' => "Page App Contenu",
                                'icon' => "icon-video",
                                'url' => $this->generateUrl("admin_navpages_list"),
                                'r' => "admin_navpages_list",
                            ],
                            [
                                'name' => "Don ",
                                'icon' => "icon-heart",
                                'url' => $this->generateUrl("admin_don"),
                                'r' => "admin_don",
                            ],
                            [
                                'name' => "Foire aux questions",
                                'icon' => "icon-help-circle",
                                'url' => $this->generateUrl("admin_faq_list"),
                                'r' => "admin_faq_list",
                            ],
                            [
                                'name' => "Formalités administratives",
                                'icon' => "icon-list",
                                'url' => $this->generateUrl("admin_todo_list"),
                                'r' => "admin_todo_list",
                            ],
                            [
                                'name' => "Période de deuil",
                                'icon' => "icon-calendar",
                                'url' => $this->generateUrl("admin_deuil"),
                                'r' => "admin_deuil",
                            ],
                            [
                                'name' => "Texte intro",
                                'icon' => "icon-align-center",
                                'url' => $this->generateUrl("admin_intro"),
                                'r' => "admin_intro",
                            ],

                            [
                                'name' => "Page contenu",
                                'icon' => "icon-layout",
                                'url' => $this->generateUrl("admin_page_list"),
                                'r' => "admin_page_list",
                            ],
                            [
                                'name' => "Contenu Emails",
                                'icon' => "icon-mail",
                                'url' => $this->generateUrl("admin_email_list"),
                                'r' => "admin_email_list",
                            ],

                        ]
                    ],
                    [
                        'name' => "Push notifications",
                        'icon' => "icon-bell",
                        'id' => 'push_notifications',
                        'hasSub' => true,
                        'subItems' => [
                            [
                                'name' => "Push notifications",
                                'icon' => "icon-bell",
                                'url' => $this->generateUrl("admin_push_notifications"),
                                'r' => "admin_push_notifications",
                            ],
                            [
                                'name' => "Avis / critiques",
                                'icon' => "icon-message-circle",
                                'url' => $this->generateUrl("admin_feedback_list"),
                                'r' => "admin_feedback_list",
                            ],
                        ]
                    ],

                ]
            ];

            $cachedMenu->set($menu);
            $this->cacheApp->save($cachedMenu);
        } else {
            $menu = $cachedMenu->get();
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            "headCard" => $headCard,
            "menu" => $menu,
            'r' => $currentRouteEncrypt
        ]);
        return $jsonResponse;
    }

    #[Route('/v-search-locations')]
    #[IsGranted('ROLE_ADMIN')]
    public function searchLocations(Request $request): Response
    {
        $ch = curl_init();
        try {
            $search = urlencode($request->get('search'));
            $params = "type=municipality";
            if($request->get('adresse') === 'true') {
                $params = "";
            }
            curl_setopt($ch, CURLOPT_URL, "https://api-adresse.data.gouv.fr/search/?q=$search&$params");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //not print
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
            $response = curl_exec($ch);
            $results = json_decode($response, true);
            if (curl_errno($ch)) {
             throw new \ErrorException();
            }
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if($http_code == intval(200)){
                curl_close($ch);
                $jsonResponse = new JsonResponse();
                //build locations
                $locations = [];
                foreach ($results["features"] as $result) {
                    $properties = $result['properties'];
                    $geometry = $result['geometry'];
                    $locations[] = [
                        'label' => $properties['label'],
                        'postcode' => $properties['postcode'],
                        'city' => $properties['city'],
                        'region' => $properties['context'],
                        'lat' => floatval($geometry['coordinates'][1]),
                        'lng' =>  floatval($geometry['coordinates'][0]),
                        'adress' =>  $properties['name'],
                    ];
                }
                $jsonResponse->setData([
                    'locations' => $locations
                ]);
                return $jsonResponse;
            } else {
                throw new \ErrorException();
            }
        } catch (\Throwable $th) {
            $jsonResponse = new JsonResponse();
            $jsonResponse->setStatusCode(500);
            $jsonResponse->setData(['message' => "impossible de récupérer l'adresse"]);
            return $jsonResponse;
        } finally {
            curl_close($ch);
        }
    }

    #[Route('/v-search-users')]
    #[IsGranted('ROLE_ADMIN')]
    public function searchUsers(Request $request): Response
    {
        $search = $request->get('search');
        $users = $this->entityManager->getRepository(User::class)->findPaginatedUsers(0, 30, $search);
        $jsonResponse = new JsonResponse();
        $userUIs = [];
        foreach ($users as $user) {
            $userUIs[] = $this->userUI->getUserProfilUI($user);
        }
        $jsonResponse->setData([
            'users' => $userUIs
        ]);
        return $jsonResponse;
    }
}
