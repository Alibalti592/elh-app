<?php
namespace App\Controller\Admin;

use App\Entity\Intro;
use App\Services\UtilsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminIntroController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly UtilsService $utilsService) {}

    #[Route('/admin/intro', name: 'admin_intro')]
    #[Route('/admin/don', name: 'admin_don')]
    public function index(Request $request): Response {
        $routeName = $request->attributes->get('_route');
        if( $routeName == 'admin_don') {
            return $this->render('admin/modules/default.twig', [
                'title' => "Don",
                'vueID' => 'admin-don'
            ]);
        }
        return $this->render('admin/modules/default.twig', [
            'title' => "Texte introduction",
            'vueID' => 'admin-intro'
        ]);
    }

    #[Route('/v-load-intro')]
    public function loadIntro(Request $request): Response
    {
        $page = $request->get('page');
        /** @var Intro $intro */
        $intro = $this->entityManager->getRepository(Intro::class)->loadIntro($page);
        if(is_null($intro)) { //ini first time
            $intro = new Intro();
            $intro->setContent("à saisir");
            $intro->setPage($page);
            $this->entityManager->persist($intro);
            $this->entityManager->flush();
        }
        $jsonResponse = new JsonResponse();
        $content = $this->utilsService->htmlDecode($intro->getContent());
        $jsonResponse->setData([
            'content' => $content,
            'contentEdit' => $content,
        ]);
        return $jsonResponse;
    }

    #[Route('/v-save-intro', methods: ['POST'])]
    public function saveTextIntro(Request $request): Response
    {
        $page = $request->get('page');
        $intro = $this->entityManager->getRepository(Intro::class)->loadIntro($page);
        $content = $this->utilsService->htmlEncodeBeforeSave($request->get('content'));
        $intro->setContent($content);
        $this->entityManager->persist($intro);
        $this->entityManager->flush();
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData();
        return $jsonResponse;
    }
}
