<?php

namespace App\Controller\Admin;

use App\Repository\UserFeedbackRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminFeedbackController extends AbstractController
{
    #[Route('/feedbacks', name: 'admin_feedback_list')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(UserFeedbackRepository $userFeedbackRepository): Response
    {
        return $this->render('admin/modules/feedback/list.twig', [
            'feedbacks' => $userFeedbackRepository->findRecent(),
        ]);
    }
}
