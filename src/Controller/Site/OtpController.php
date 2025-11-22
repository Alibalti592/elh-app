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

        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        $otp = random_int(100000, 999999);

        $templateParams = [
            'appURl'     => 'https://muslim-connect.fr',
            'title'      => 'Vérification de votre email',
            'shortDesc'  => 'Votre code OTP pour vérifier votre email',
            'text'       => sprintf('Votre code de vérification est : <strong>%s</strong>', $otp),
            'action'     => null, 
            'actionText' => null,
            'userName'   => $user->getFullName(), 
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

        return new JsonResponse([
            'message' => 'OTP sent successfully',
            'email'   => $email,
            'otp'     => $otp 
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
    $codeSent    = $data['codeSent'] ?? null;
    $email       = $data['email'] ?? null; 

    // if (!$codeRecived || !$codeSent) {
    //     return new JsonResponse(['error' => 'Both codes are required'], 400);
    // }

    
    if (!$email) {
        return new JsonResponse(['error' => 'Email is required to verify OTP'], 400);
    }

    $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
    if (!$user) {
        return new JsonResponse(['error' => 'User not found'], 404);
    }

    if ($codeRecived === $codeSent) {
        $user->setStatus('active');
        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse([
        'success' => true,
        'message' => 'OTP verified and user activated',
        'status'  => $user->getStatus(),
        'email'   => $user->getEmail(),
        // or even the full user if you want
    ]);
    }

    return new JsonResponse(['success' => false, 'message' => 'OTP does not match'], 400);
}

    
}
