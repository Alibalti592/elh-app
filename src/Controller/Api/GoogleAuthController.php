<?php

namespace App\Controller\Api; // correct namespace
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Google_Client;

class GoogleAuthController extends AbstractController
{
    #[Route('/elh-api/auth/google', name: 'google_auth', methods: ['POST'])]
    public function googleAuth(
        Request $request,
        EntityManagerInterface $em,
        JWTTokenManagerInterface $jwtManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $idToken = $data['idToken'] ?? null;

        if (!$idToken) {
            return $this->json(['error' => 'Missing Google ID token'], 400);
        }

        // Verify with Google
        $client = new Google_Client(['client_id' => $_ENV['GOOGLE_CLIENT_ID']]);
        $payload = $client->verifyIdToken($idToken);

        if (!$payload) {
            return $this->json(['error' => 'Invalid Google token'], 401);
        }

        $email = $payload['email'];
        $name = $payload['name'];

        // Look up or create user
        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            $user = new User();
            $user->setEmail($email);
            $user->setName($name);
            // you can also set ROLE_USER
            $user->setRoles(['ROLE_USER']);
            $em->persist($user);
            $em->flush();
        }

        // Create JWT for this user
        $token = $jwtManager->create($user);

        return $this->json([
            'token' => $token,
            'user' => [
                'email' => $user->getEmail(),
                'name' => $user->getName(),
            ]
        ]);
    }
}
