<?php

namespace App\Controller\Site;

use App\Entity\Intro;
use App\Entity\Page;
use App\Services\UtilsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class SiteController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly UtilsService $utilsService) {

    }

    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return $this->render('site/modules/page/home.twig', [
        ]);
    }

    #[Route('/mentions-legales', name: "mentions_legales")]
    public function mentions(): Response
    {
        $page = $this->entityManager->getRepository(Page::class)->findOneBy([
            'slug' => 'mentions'
        ]);
        return $this->render('site/modules/page/default.twig', [
            'content' =>  $this->utilsService->htmlDecode($page->getContent()),
            'title' => $page->getTitle(),
            'noindex' => true
        ]);
    }

    #[Route('/cgu', name: "cgu")]
    public function cgu(): Response
    {
        $page = $this->entityManager->getRepository(Page::class)->findOneBy([
            'slug' => 'cgu'
        ]);
        return $this->render('site/modules/page/default.twig', [
            'content' =>  $this->utilsService->htmlDecode($page->getContent()),
            'title' => $page->getTitle(),
            'noindex' => true
        ]);
    }


    #[Route('/get-intro-text', name: "text-intro")]
    public function getIntro(): Response
    {
        $jsonResponse = new JsonResponse();
        $intro = $this->entityManager->getRepository(Intro::class)->loadIntro();
        $content = 'Bienvenue sur Muslim Connect !';
        if(!is_null($intro)) {
            $content = $this->utilsService->htmlDecode($intro->getContent());
        }
        $jsonResponse->setData([
            'text' => $content
        ]);
        return $jsonResponse;
    }

    #[Route('/app', name: 'smart_app_redirect', methods: ['GET'])]
    public function appSmartRedirect(Request $request): Response
    {
        $iosUrl     = 'https://apps.apple.com/us/app/muslim-connect/id6478540540';
        $androidUrl = 'https://play.google.com/store/apps/details?id=com.elh.app&pli=1';

        $ua = strtolower($request->headers->get('User-Agent', ''));

        // 1. Détecter si c'est un bot (réseaux sociaux pour l'aperçu)
        $isBot = preg_match('/bot|crawl|slurp|spider|facebookexternalhit|snapchat|whatsapp|link|twitter/i', $ua);

        if ($isBot) {
            $baseUrl = $request->getSchemeAndHttpHost();
            $imageUrl = $baseUrl . '/images/logo-full-bg.png';
            
            return new Response('
                <!DOCTYPE html>
                <html lang="fr">
                <head>
                    <meta charset="UTF-8">
                    <title>Muslim Connect</title>
                    <meta property="og:title" content="Muslim Connect" />
                    <meta property="og:description" content="Telecharge Muslim Connect la solution digitale 100% gratuite pour organiser, préserver et transmettre en toute sérénité tes engagements, tes dettes et tes dernières volontés. Pour que tout soit clair… même après ton départ." />
                    <meta property="og:image" content="'.$imageUrl.'" />
                    <meta property="og:type" content="website" />
                    <meta name="twitter:card" content="summary_large_image">
                </head>
                <body>
                    <p>Redirection vers l\'application...</p>
                    <script>
                        // Au cas où un humain arrive ici sans être redirigé par le serveur
                        window.location.href = (navigator.userAgent.match(/iPhone|iPad|iPod/i)) ? "'.$iosUrl.'" : "'.$androidUrl.'";
                    </script>
                </body>
                </html>
            ');
        }

        // 2. Détection de l'OS pour les utilisateurs réels
        $isAndroid = str_contains($ua, 'android');
        $isIOS     = str_contains($ua, 'iphone') || str_contains($ua, 'ipad') || str_contains($ua, 'ipod');

        $target = $isIOS ? $iosUrl : $androidUrl;

        return new RedirectResponse($target, 302, [
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma'        => 'no-cache',
        ]);
    }

}
