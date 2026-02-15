<?php

namespace App\Controller\Api;

use App\Entity\UserFeedback;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FeedbackController extends AbstractController
{
    #[Route('/submit-feedback', name: 'submit_feedback', methods: ['POST'])]
    public function submitFeedback(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $currentUser = $this->getUser();
        if (!$currentUser) {
            return $this->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        $comment = trim((string) $request->get('comment', ''));
        if ($comment === '') {
            $payload = json_decode((string) $request->getContent(), true);
            if (is_array($payload)) {
                $comment = trim((string) ($payload['comment'] ?? ''));
            }
        }

        if ($comment === '' || mb_strlen($comment) < 3) {
            return $this->json(['error' => 'Commentaire trop court'], 400);
        }

        if (mb_strlen($comment) > 3000) {
            return $this->json(['error' => 'Commentaire trop long (max 3000 caractères)'], 400);
        }

        $feedback = new UserFeedback();
        $feedback->setUser($currentUser);
        $feedback->setComment($comment);
        $feedback->setCreatedAt(new \DateTimeImmutable());

        $entityManager->persist($feedback);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Merci pour ton retour',
            'id' => $feedback->getId(),
        ]);
    }
}
