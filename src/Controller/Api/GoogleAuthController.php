<?php

namespace App\Controller\Api;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Google_Client;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class GoogleAuthController extends AbstractController
{
    #[Route('/elh-api/auth/google', name: 'google_auth', methods: ['POST'])]
    public function googleAuth(
        Request $request,
        EntityManagerInterface $em,
        JWTTokenManagerInterface $jwtManager
    ): JsonResponse {
        // 1) Lire le JSON
        $data = json_decode($request->getContent(), true) ?? [];
        $idToken = $data['idToken'] ?? null;
        if (!$idToken) {
            return $this->json(['error' => 'Missing Google ID token'], 400);
        }

        // 2) Vérifier le token Google
        //    Pas besoin de secret pour verifyIdToken.
        $client = new Google_Client();
        $payload = $client->verifyIdToken($idToken);
        if (!$payload) {
            return $this->json(['error' => 'Invalid Google token'], 401);
        }

        // 3) Vérifier l’audience (aud)
        //    Si ton app Flutter passe serverClientId = WEB_CLIENT_ID,
        //    alors le aud doit être le client Web.
        $aud = $payload['aud'] ?? null;
        $allowedAudiences = array_values(array_filter([
            $_ENV['GOOGLE_WEB_CLIENT_ID'] ?? null,     // ex: ...apps.googleusercontent.com
            $_ENV['GOOGLE_ANDROID_CLIENT_ID'] ?? null, // optionnel si tu veux autoriser l’audience Android
            $_ENV['GOOGLE_IOS_CLIENT_ID'] ?? null,     // optionnel si tu veux autoriser l’audience iOS
        ]));
        if (!in_array($aud, $allowedAudiences, true)) {
            return $this->json(['error' => 'Token audience mismatch'], 401);
        }

        // 4) Vérifier l’email et sa vérification
        if (empty($payload['email']) || empty($payload['email_verified'])) {
            return $this->json(['error' => 'Unverified Google account'], 401);
        }

        $email = $payload['email'];
        $fullName  = trim((string) ($payload['name'] ?? ''));
        if ($fullName === '') {
            $fullName = explode('@', $email)[0];
        }
        $nameParts = preg_split('/\s+/', $fullName) ?: [];
        $firstname = $nameParts[0] ?? 'Utilisateur';
        $lastname = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : 'Google';

        // 5) Trouver ou créer l’utilisateur
        $repo = $em->getRepository(User::class);
        $user = $repo->findOneBy(['email' => $email]);

        if (!$user) {
            $user = new User();
            $user->setEmail($email);
            $user->setFirstname($firstname);
            $user->setLastname($lastname);
            $user->setRoles(['ROLE_USER']);
            $user->setAuthProvider('google');
            $user->setStatus('active');
            $user->setOtpCode(null);
            $user->setOtpExpiresAt(null);
            $em->persist($user);
        } else {
            if ($user->getFirstname() === null || trim($user->getFirstname()) === '') {
                $user->setFirstname($firstname);
            }
            if ($user->getLastname() === null || trim($user->getLastname()) === '') {
                $user->setLastname($lastname);
            }
            $user->setAuthProvider('google');
            $user->setStatus('active');
            $user->setOtpCode(null);
            $user->setOtpExpiresAt(null);
        }

        // 6) Sauvegarde
        $em->flush();

        // 7) Générer le JWT applicatif
        $token = $jwtManager->create($user);

        return $this->json([
            'token' => $token,
            'user'  => [
                'email' => $user->getEmail(),
                'name'  => $user->getFullname(),
            ],
        ]);
    }
}
