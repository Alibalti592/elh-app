<?php

namespace App\Controller\Api;

use App\Repository\MobileAppVersionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AppVersionController extends AbstractController
{
    private const STORE_URLS = [
        'ios' => 'https://apps.apple.com/us/app/muslim-connect/id6478540540',
        'android' => 'https://play.google.com/store/apps/details?id=com.elh.app&pli=1',
    ];

    #[Route('/app-version', name: 'app_version', methods: ['GET'])]
    public function getAppVersion(
        Request $request,
        MobileAppVersionRepository $mobileAppVersionRepository
    ): JsonResponse {
        $platform = strtolower(trim((string) $request->query->get('platform', '')));

        if (!in_array($platform, ['ios', 'android'], true)) {
            return $this->json([
                'message' => 'Plateforme invalide. Valeurs autorisées: ios, android.',
            ], 400);
        }

        $versionConfig = $mobileAppVersionRepository->findForPlatform($platform);
        if (!$versionConfig) {
            return $this->json([
                'message' => 'Aucune version configurée pour cette plateforme.',
                'platform' => $platform,
            ], 404);
        }

        return $this->json([
            'platform' => $platform,
            'latestVersion' => $versionConfig->getVersion(),
            'storeUrl' => self::STORE_URLS[$platform] ?? null,
        ]);
    }
}

