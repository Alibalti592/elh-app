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
public function appSmartRedirect(Request $request): RedirectResponse
{
    // 🔧 Put your real store URLs here
    $iosUrl     = 'https://apps.apple.com/app/id0000000000'; // e.g., https://apps.apple.com/app/id1234567890
    $androidUrl = 'https://play.google.com/store/apps/details?id=com.yourcompany.yourapp';

    // Optional: allow manual override with ?platform=ios | android (useful for testing/QRs)
    $override = strtolower((string) $request->query->get('platform', ''));
    if ($override === 'ios') {
        return new RedirectResponse($iosUrl, 302, [
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma'        => 'no-cache',
        ]);
    }
    if ($override === 'android') {
        return new RedirectResponse($androidUrl, 302, [
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma'        => 'no-cache',
        ]);
    }

    // Simple User-Agent detection
    $ua = strtolower($request->headers->get('User-Agent', ''));

    $isAndroid = str_contains($ua, 'android');
    $isIOS     = str_contains($ua, 'iphone') || str_contains($ua, 'ipad') || str_contains($ua, 'ipod');

    $target = $isAndroid ? $androidUrl : ($isIOS ? $iosUrl : null);

    // If we couldn't detect, you can either:
    // 1) default to a small landing page with both links, or
    // 2) pick one (commonly Android) as default.
    if (!$target) {
        // Option A: default to a landing page you host (uncomment and set path)
        // return $this->redirectToRoute('home'); // or render a page with both links

        // Option B: default to Android (change if you prefer iOS)
        $target = $androidUrl;
    }

    return new RedirectResponse($target, 302, [
        'Cache-Control' => 'no-store, no-cache, must-revalidate',
        'Pragma'        => 'no-cache',
    ]);
}

}
