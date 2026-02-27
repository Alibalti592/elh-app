<?php

namespace App\Controller\Admin;

use App\Entity\NotifToSend;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminPushNotificationController extends AbstractController
{
    private const VIEW_OPTIONS = [
        '' => 'Aucune redirection',
        'chatview' => 'Chat',
        'obligation_list_view' => 'Dettes / Obligations',
        'carte_list_view' => 'Cartes',
        'pardon_view' => 'Pardon',
        'mosque_notif_view' => 'Mosquée',
        'pompe_noitif_view' => 'Pompe funèbre',
        'shared_testament' => 'Testament partagé',
        'pray' => 'Prière',
        'tranche' => 'Tranches',
        'feedback_view' => 'Avis / critiques',
    ];

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route('/push-notifications', name: 'admin_push_notifications')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(): Response
    {
        return $this->render('admin/modules/push-notifications/list.twig', [
            'views' => self::VIEW_OPTIONS,
        ]);
    }

    #[Route('/v-send-push-notification', name: 'admin_push_notifications_send', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function send(Request $request): Response
    {
        $title = trim((string) $request->get('title'));
        $message = trim((string) $request->get('message'));
        $view = (string) $request->get('view', '');
        $view = array_key_exists($view, self::VIEW_OPTIONS) ? $view : '';

        if ($title === '' || $message === '') {
            $this->addFlash('error', 'Titre et message sont obligatoires.');
            return new RedirectResponse($this->generateUrl('admin_push_notifications'));
        }

        $now = new \DateTime();
        $count = 0;
        $batchSize = 200;

        $qb = $this->entityManager->createQueryBuilder();
        $users = $qb
            ->select('u')
            ->from(User::class, 'u')
            ->andWhere('u.enabled = :enabled')
            ->andWhere('u.deletedAt IS NULL')
            ->setParameter('enabled', true)
            ->getQuery()
            ->toIterable();

        foreach ($users as $user) {
            $notif = new NotifToSend();
            $notif->setUser($user);
            $notif->setTitle($title);
            $notif->setMessage($message);
            $notif->setSendAt(clone $now);
            $notif->setType('admin_push');
            $notif->setStatus('pending');
            $notif->setIsRead(false);
            if ($view !== '') {
                $notif->setView($view);
            }
            $this->entityManager->persist($notif);
            $count++;

            if (($count % $batchSize) === 0) {
                $this->entityManager->flush();
                $this->entityManager->clear();
            }
        }

        $this->entityManager->flush();
        $this->entityManager->clear();

        $this->addFlash('success', "Notification créée pour {$count} utilisateurs.");
        return new RedirectResponse($this->generateUrl('admin_push_notifications'));
    }
}
