<?php

namespace App\Controller\Site;

use App\Entity\Resetpassword;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\AppAuthenticator;
use App\Services\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class RegistrationController extends AbstractController
{
    public function __construct(private readonly UrlGeneratorInterface $urlGenerator,
                                private readonly NotificationService $notificationService, private readonly EntityManagerInterface $entityManager) {}

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher,
                             UserAuthenticatorInterface $userAuthenticator, AppAuthenticator $authenticator,
                             EntityManagerInterface $entityManager): Response
    {
        $env = $this->getParameter('kernel.environment');
        if($env != 'dev') {
            return new RedirectResponse($this->urlGenerator->generate('app_login'));
        }
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render('site/modules/userSecurity/register.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/ini-reset-password')]
    public function iniResetPassword(Request $request) {
        $email = trim($request->get('email'));
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => $email
        ]);
        $jsonResponse = new JsonResponse();
        if(is_null($user)) {
            $jsonResponse->setStatusCode(500);
            $jsonResponse->setData([
                'message' => 'Compte introuvable avec cet email !'
            ]);
            return $jsonResponse;
        }
        $reset = $this->entityManager->getRepository(Resetpassword::class)->findOneBy([
            'user' => $user
        ]);
        if(!is_null($reset)) {
            //check if expired  !!
            if($reset->isExpired()) {
                $this->entityManager->remove($reset);
                $this->entityManager->flush();
            } else {
                return $jsonResponse;
            }
        }
        $reset = new Resetpassword();
        $reset->setUser($user);
        $this->entityManager->persist($reset);
        $this->entityManager->flush();
        $this->notificationService->resetPassword($user, $reset->getCode());
        return $jsonResponse;
    }

    #[Route('/confirm-reset-password')]
    public function confirmResetPassword(Request $request, UserPasswordHasherInterface $userPasswordHasher) {
        $datas = json_decode($request->getContent(), true);
        $email = trim($datas['email']);
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => $email
        ]);
        $jsonResponse = new JsonResponse();
        if(is_null($user)) {
            $jsonResponse->setStatusCode(500);
            $jsonResponse->setData([
                'message' => 'Compte introuvable avec cet email'
            ]);
            return $jsonResponse;
        }
        $reset = $this->entityManager->getRepository(Resetpassword::class)->findOneBy([
            'user' => $user
        ]);
        if((!is_null($reset) && $reset->isExpired() || $reset->getCode() != $datas['code']) || is_null($reset)) {
            $jsonResponse->setStatusCode(500);
            $jsonResponse->setData([
                'message' => 'Code non valide !'
            ]);
            return $jsonResponse;
        }
        $user->setPassword(
            $userPasswordHasher->hashPassword(
                $user,
                $datas['password']
            )
        );
        $this->entityManager->persist($user);
        $this->entityManager->remove($reset);
        $this->entityManager->flush();

        return $jsonResponse;
    }

}
