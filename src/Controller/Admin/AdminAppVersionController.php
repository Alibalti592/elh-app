<?php

namespace App\Controller\Admin;

use App\Entity\MobileAppVersion;
use App\Repository\MobileAppVersionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminAppVersionController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MobileAppVersionRepository $mobileAppVersionRepository
    ) {
    }

    #[Route('/app-versions', name: 'admin_app_versions')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(): Response
    {
        $versions = [
            'ios' => '',
            'android' => '',
        ];
        $dbError = null;

        try {
            $ios = $this->mobileAppVersionRepository->findForPlatform('ios');
            if ($ios) {
                $versions['ios'] = $ios->getVersion();
            }

            $android = $this->mobileAppVersionRepository->findForPlatform('android');
            if ($android) {
                $versions['android'] = $android->getVersion();
            }
        } catch (\Throwable $e) {
            $dbError = 'Module versions non initialisé en base. Exécute la migration doctrine.';
        }

        return $this->render('admin/modules/app-version/list.twig', [
            'versions' => $versions,
            'dbError' => $dbError,
        ]);
    }

    #[Route('/v-save-app-versions', name: 'admin_app_versions_save', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function save(Request $request): RedirectResponse
    {
        $iosVersion = trim((string) $request->request->get('ios_version', ''));
        $androidVersion = trim((string) $request->request->get('android_version', ''));

        if ($iosVersion === '' || $androidVersion === '') {
            $this->addFlash('error', 'Les versions iOS et Android sont obligatoires.');
            return new RedirectResponse($this->generateUrl('admin_app_versions'));
        }

        if (!$this->isValidVersion($iosVersion) || !$this->isValidVersion($androidVersion)) {
            $this->addFlash('error', 'Format invalide. Utilise un format du type 1.1.3.');
            return new RedirectResponse($this->generateUrl('admin_app_versions'));
        }

        try {
            $iosConfig = $this->getOrCreateVersionConfig('ios');
            $iosConfig->setVersion($iosVersion);

            $androidConfig = $this->getOrCreateVersionConfig('android');
            $androidConfig->setVersion($androidVersion);

            $this->entityManager->persist($iosConfig);
            $this->entityManager->persist($androidConfig);
            $this->entityManager->flush();

            $this->addFlash('success', 'Versions mobile mises à jour.');
        } catch (\Throwable $e) {
            $this->addFlash('error', 'Impossible de sauvegarder les versions. Vérifie la base de données.');
        }

        return new RedirectResponse($this->generateUrl('admin_app_versions'));
    }

    private function getOrCreateVersionConfig(string $platform): MobileAppVersion
    {
        $config = $this->mobileAppVersionRepository->findForPlatform($platform);
        if ($config) {
            return $config;
        }

        $config = new MobileAppVersion();
        $config->setPlatform($platform);

        return $config;
    }

    private function isValidVersion(string $version): bool
    {
        return (bool) preg_match('/^[0-9A-Za-z._-]{1,30}$/', $version);
    }
}
