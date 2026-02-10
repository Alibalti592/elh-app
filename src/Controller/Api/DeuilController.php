<?php
namespace App\Controller\Api;

use App\Entity\CarteShare;
use App\Entity\Deuil;
use App\Entity\DeuilDate;
use App\Entity\Jeun;
use App\Entity\MosqueFavorite;
use App\Entity\Obligation;
use App\Entity\Salat;
use App\Entity\SalatShare;
use App\Services\CRUDService;
use App\Services\UtilsService;
use App\UIBuilder\ObligationUI;
use App\UIBuilder\MosqueUI;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DeuilController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly UtilsService $utilsService,
                                private readonly CRUDService $CRUDService, private readonly ObligationUI $obligationUI,
                                private readonly MosqueUI $mosqueUI) {}

    #[Route('/load-deuil-dates')]
    public function loadDeuilDates(Request $request): Response
    {
        $currentUser = $this->getUser();
        $deuilDates = $this->entityManager->getRepository(DeuilDate::class)->findNextDeuilDates($currentUser);
        $deuilDateUIs = [];
        /** @var DeuilDate $deuilDate */
        foreach ($deuilDates as $deuilDate) {
            $deuilDateUIs[] = [
                'id' => $deuilDate->getId(),
                'date' => $deuilDate->getEndDate()->format('d/m/Y'),
            ];
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'deuilDates' => $deuilDateUIs,
        ]);
        return $jsonResponse;
    }


    #[Route('/load-deuil')]
    public function loadDeuildate(Request $request): Response
    {
        $type = $request->get('type');
        $endDateNormal = new \DateTime($request->get('date'));
        $endDateNormal->modify('+3 days');
        $endDate2Display = $this->utilsService->getReadableDate($endDateNormal);

        //date epouse 4 mois et 10j
        $endDate = new \DateTime($request->get('date'));
        $endDate->modify('+128 days');

        $deuil = $this->entityManager->getRepository(Deuil::class)->loadDeuil($type);
        $endDateDisplay = $this->utilsService->getReadableDate($endDate);
        $basetext = $this->utilsService->htmlDecode($deuil->getContent());
        $content = str_replace("{date_plus_trois_jour}", $endDate2Display, $basetext);
        $content = str_replace("{datefin}", $endDateDisplay, $content);
        $jsonResponse = new JsonResponse();
        $ref = time();
        $endDateToSave = $endDate;
        if($type == 'family') {
            $endDateToSave = $endDateNormal;
        }
        $jsonResponse->setData([
            'content' => $content,
            'endDate' => $endDateToSave->format("d/m/Y"),
            'ref' => $ref
        ]);
        return $jsonResponse;
    }

    #[Route('/save-deuil-date', methods: ['POST'])]
    public function savedaten(Request $request): Response
    {
        $currentUser = $this->getUser();
        $ref = $request->get('ref');
        $deuilDates = $this->entityManager->getRepository(DeuilDate::class)->findBy([
            'user' => $currentUser,
            'ref' => $ref
        ]);
        foreach ($deuilDates as $deuilDate) {
            $this->entityManager->remove($deuilDate);
        }
        $this->entityManager->flush();
        if($request->get('endDate') != null) {
            $date = \DateTime::createFromFormat('d/m/Y', $request->get('endDate'));
            $date->setTime(23, 59, 59);
            $deuilDate = $this->entityManager->getRepository(DeuilDate::class)->findOneBy([
                'user' => $currentUser,
                'endDate' => $date
            ]);
            if(is_null($deuilDate)) {
                $deuilDate = new DeuilDate();
                $deuilDate->setUser($currentUser);
                $deuilDate->setEndDate($date);
                $deuilDate->setRef($ref);
                $this->entityManager->persist($deuilDate);
            }
        }
        $this->entityManager->flush();
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData();
        return $jsonResponse;
    }

    #[Route('/delete-deuil-date', methods: ['POST'])]
    public function deletedaten(Request $request): Response
    {
        $currentUser = $this->getUser();
        $deuilDateId = $request->get('deuilDateId');
        $deuilDate = $this->entityManager->getRepository(DeuilDate::class)->findOneBy([
            'user' => $currentUser,
            'id' => $deuilDateId
        ]);
        if(!is_null($deuilDate)) {
            $this->entityManager->remove($deuilDate);
            $this->entityManager->flush();
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData();
        return $jsonResponse;
    }


    #[Route('/load-dashboard-datas')]
    public function loadDashboardDatas(Request $request): Response
    {
        $currentUser = $this->getUser();
        $deuilDates = $this->entityManager->getRepository(DeuilDate::class)->findNextDeuilDates($currentUser);
        $salats = $this->entityManager->getRepository(Salat::class)->getSalatsOfuser($currentUser, true);
        $allSalatIds = [];
        $nbSalats = count($salats);
        foreach ($salats as $salat) {
            $allSalatIds[] = $salat->getId();
        }
        $mosqueIds = $this->entityManager->getRepository(MosqueFavorite::class)->findMosqueFavoriteIds($currentUser);
        $salatsOfMosques = $this->entityManager->getRepository(Salat::class)->getSalatsInMosques($mosqueIds, $allSalatIds);
        $nbSalats += count($salatsOfMosques);
        foreach ($salatsOfMosques as $salat) {
            $allSalatIds[] = $salat->getId();
        }
        //salats shared
        $salatShares = $this->entityManager->getRepository(SalatShare::class)->getSalatsSharedOfuser($currentUser, true);
        foreach ($salatShares as $salatShares) {
            if(!in_array($salatShares->getSalat()->getId(), $allSalatIds)) {
                $nbSalats += 1;
            }
        }

        $nbOnms = $this->entityManager->getRepository(Obligation::class)
            ->countObligationToRefund($currentUser, 'onm');
        $nbJeds = $this->entityManager->getRepository(Obligation::class)
            ->countObligationToRefund($currentUser, 'jed');
//        $nbAmanas = $this->entityManager->getRepository(Obligation::class)
//            ->countObligationToRefund($currentUser, 'amana');
        $nbCartes = $this->entityManager->getRepository(CarteShare::class)
            ->countSharedCartes($currentUser);
        $nbJeun = 0;
        $jeun = $this->entityManager->getRepository(Jeun::class)->findOneBy([
            'createdBy' => $currentUser
        ]);
        if(!is_null($jeun)) {
            $nbJeun = $jeun->getTotalRemainingDays();
        }
        $favoriteMosques = [];
        $mosqueFavs = $this->entityManager->getRepository(MosqueFavorite::class)->findMosqueFavorited($currentUser);
        foreach ($mosqueFavs as $mosqueFav) {
            $mosque = $mosqueFav->getMosque();
            $mosqueUI = $this->mosqueUI->getMosque($mosque);
            $mosqueUI['isFavorite'] = true;
            $favoriteMosques[] = $mosqueUI;
        }


        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'nbSalats' => $nbSalats,
            'nbOnms' => $nbOnms,
            'nbAmanas' => $nbCartes, //pas utilisé juste nom pas changé
            'nbJeds' => $nbJeds,
            'nbCartes' => $nbCartes,
            'nbDeuils' => count($deuilDates),
            'nbJeun' => $nbJeun,
            'favoriteMosques' => $favoriteMosques,
        ]);
        return $jsonResponse;
    }

}
