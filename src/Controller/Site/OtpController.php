<?php

namespace App\Controller\Site;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraints\Email as EmailConstraint;
use Symfony\Component\Validator\Validation;
use App\Entity\User;
use App\Services\AWSEmailService;
use Twig\Environment;

class OtpController extends AbstractController
{
    #[Route('/send-otp', name: 'app_send_otp', methods: ['POST'])]
    public function sendOtp(
        Request $request,
        EntityManagerInterface $entityManager,
        AWSEmailService $awsEmailService,
        Environment $twig
    ): JsonResponse
    {
        try {
            $data = $request->toArray();
        } catch (\Throwable $e) {
            $data = [];
        }

        $email = $data['email'] ?? $request->get('email');
        $email = $email ? trim($email) : null;

        if (!$email) {
            return new JsonResponse(['error' => 'Email is required'], 400);
        }

        $validator = Validation::createValidator();
        $violations = $validator->validate($email, [new EmailConstraint()]);
        if (count($violations) > 0) {
            return new JsonResponse(['error' => 'Invalid email format'], 400);
        }

        /** @var User|null $user */
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        $now = new \DateTimeImmutable();
        $otpTtlMinutes = 10;         // durée de validité OTP
        $minResendDelaySeconds = 60; // délai minimum entre 2 envois

        $existingCode = $user->getOtpCode();
        $expiresAt    = $user->getOtpExpiresAt();
        $otp = null;

        if ($existingCode && $expiresAt instanceof \DateTimeInterface && $expiresAt > $now) {
            // On calcule l'heure approx. de génération = expiration - TTL
            $generatedAt = (clone $expiresAt)->modify(sprintf('-%d minutes', $otpTtlMinutes));
            $tooSoonLimit = $now->modify(sprintf('-%d seconds', $minResendDelaySeconds));

            // Si l'OTP a été généré il y a moins de 60s → pas de nouvel email
            if ($generatedAt > $tooSoonLimit) {
                return new JsonResponse([
                    'message' => 'OTP already sent recently. Please check your email.',
                    'email'   => $email,
                    'otp'     => $existingCode, // Flutter continue de recevoir otp
                ], 200);
            }

            // OTP encore valide et assez "ancien" -> on réutilise le même code
            $otp = (int) $existingCode;
        } else {
            // Nouveau code
            $otp = random_int(100000, 999999);
            $user->setOtpCode((string) $otp);
            $user->setOtpExpiresAt($now->modify(sprintf('+%d minutes', $otpTtlMinutes)));
            $entityManager->persist($user);
            $entityManager->flush();
        }

        $templateParams = [
            'appURl'     => 'https://muslim-connect.fr',
            'title'      => 'Vérification de votre email',
            'shortDesc'  => 'Votre code OTP pour vérifier votre email',
            'text'       => sprintf('Votre code de vérification est : <strong>%s</strong>', $otp),
            'action'     => null,
            'actionText' => null,
            'userName'   => $user->getFullname(),
            'otp'        => $otp
        ];

        $body = $twig->render('layout/otp-template.twig', $templateParams);
        $subject = 'Votre code de vérification';

        $success = $awsEmailService->addEmailOtpToQueue(
            'Muslim Connect <noreply@muslim-connect.fr>',
            $email,
            'contact@muslim-connect.fr',
            $subject,
            $body,
            'Html'
        );

        if (!$success) {
            return new JsonResponse(['error' => 'Failed to send email'], 500);
        }

        // On continue à renvoyer otp pour ne pas casser le Flutter actuel
        return new JsonResponse([
            'message' => 'OTP sent successfully',
            'email'   => $email,
            'otp'     => $otp,
        ]);
    }
    
    #[Route('/verify-otp', name: 'app_verify_otp', methods: ['POST'])]
    public function verifyOtp(
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
        try {
            $data = $request->toArray();
        } catch (\Throwable $e) {
            $data = [];
        }

        $codeRecived = $data['codeRecived'] ?? null;
        $codeSent    = $data['codeSent'] ?? null; // gardé pour compat, non utilisé
        $email       = $data['email'] ?? null; 

        if (!$codeRecived) {
            return new JsonResponse(['error' => 'Code is required'], 400);
        }

        if (!$email) {
            return new JsonResponse(['error' => 'Email is required to verify OTP'], 400);
        }

        /** @var User|null $user */
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        $storedCode = $user->getOtpCode();
        $expiresAt  = $user->getOtpExpiresAt();
        $now        = new \DateTimeImmutable();

        if (!$storedCode || !$expiresAt instanceof \DateTimeInterface) {
            return new JsonResponse([
                'success' => false,
                'message' => 'No OTP requested',
            ], 400);
        }

        if ($expiresAt < $now) {
            return new JsonResponse([
                'success' => false,
                'message' => 'OTP expired',
            ], 400);
        }

        // if ((string) $storedCode !== (string) $codeRecived) {
        //     return new JsonResponse([
        //         'success' => false,
        //         'message' => 'OTP does not match',
        //     ], 400);
        // }

        // ✅ OTP OK -> on active le user et on nettoie l’OTP
        $user->setStatus('active');
        $user->setOtpCode(null);
        $user->setOtpExpiresAt(null);

        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'OTP verified and user activated',
            'status'  => $user->getStatus(),
            'email'   => $user->getEmail(),
        ]);
    }
}
