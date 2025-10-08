<?php

namespace App\Controller\Site;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager, private UrlGeneratorInterface $urlGenerator) {

    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
         if ($this->getUser()) {
             $env = $this->getParameter('kernel.environment');
             if($env == 'dev') {
//                 $user = $this->getUser();
//                 $user->setRoles(['ROLE_ADMIN']);
//                 $this->entityManager->persist($user);
//                 $this->entityManager->flush();
             }
             if($this->isGranted('ROLE_ADMIN')) {
                 return new RedirectResponse($this->urlGenerator->generate('admin_user_list'));
             } else {
                 $this->addFlash('error', "Pour accéder à votre compte connectez-vous à l'application");
                 return new RedirectResponse($this->urlGenerator->generate('home'));
             }
         }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('site/modules/userSecurity/login.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
