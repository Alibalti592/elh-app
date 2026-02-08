<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminPushNotificationController extends AbstractController
{
    #[Route('/push-notifications', name: 'admin_push_notifications')]
    public function index(): Response
    {
        return $this->render('admin/modules/push-notifications/list.twig', []);
    }
}
