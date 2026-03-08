<?php
namespace App\Command;

use App\Entity\NotifToSend;
use App\Services\FcmNotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendNotifCommand extends Command
{
    use LockableTrait;

    protected static $defaultName = 'app:sendNotif';

    private $entityManager;
    private $fcmNotificationService;

    public function __construct(FcmNotificationService $fcmNotificationService, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->fcmNotificationService = $fcmNotificationService;
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        // tu peux ajouter une description si tu veux
        $this->setDescription('Envoie les notifications en attente via FCM');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        set_time_limit(600);
        if (!$this->lock()) {
            return Command::SUCCESS;
        }

        $notifsToSend = $this->entityManager->getRepository(NotifToSend::class)->findNotifToSend();
        $sentPrayKeys = [];

        /** @var NotifToSend $notifToSend */
        foreach ($notifsToSend as $notifToSend) {
            if ($notifToSend->getView() === 'pray') {
                $sendAt = $notifToSend->getSendAt();
                $key = implode('|', [
                    $notifToSend->getUser()?->getId(),
                    $notifToSend->getType(),
                    $sendAt?->getTimestamp(),
                ]);

                if (isset($sentPrayKeys[$key])) {
                    $this->entityManager->remove($notifToSend);
                    $this->entityManager->flush();
                    continue;
                }

                $sentPrayKeys[$key] = true;
            }

            if ($notifToSend->getView() === 'pray') {
                if (!$this->claimPrayNotifGroup($notifToSend)) {
                    continue;
                }
            }

            $data = [];

            $datas = json_decode($notifToSend->getDatas() ?: '[]', true);
            if (is_array($datas)) {
                $data = $datas;
            }

            if (!is_null($notifToSend->getView())) {
                $data['view'] = $notifToSend->getView();
            }
            $data['notifId'] = (string)$notifToSend->getId();

            // Compat: keep legacy action buttons if this view is used.
            if (($data['view'] ?? null) === 'tranche_action' && !isset($data['actions'])) {
                $data['actions'] = [
                    ['id' => 'accepter', 'title' => 'Accepter'],
                    ['id' => 'refuser', 'title' => 'Refuser'],
                ];
            }

            if (empty($data)) {
                $data = null;
            }

            $this->fcmNotificationService->sendFcmDefaultNotification(
                $notifToSend->getUser(),
                $notifToSend->getTitle(),
                $notifToSend->getMessage(),
                $data
            );

            if ($notifToSend->getView() === 'pray') {
                $this->cleanupPrayNotifGroup($notifToSend);
            } else {
                $notifToSend->setStatus('sent');
                $notifToSend->setIsRead(false);
            }

        
            $this->entityManager->flush();
        }

        return Command::SUCCESS;
    }

    private function claimNotif(NotifToSend $notifToSend): bool
    {
        $qb = $this->entityManager->createQueryBuilder();
        $updated = $qb
            ->update(NotifToSend::class, 'n')
            ->set('n.status', ':sending')
            ->andWhere('n.id = :id')
            ->andWhere('n.status = :pending')
            ->setParameter('sending', 'sending')
            ->setParameter('pending', 'pending')
            ->setParameter('id', $notifToSend->getId())
            ->getQuery()
            ->execute();

        return $updated === 1;
    }

    private function claimPrayNotifGroup(NotifToSend $notifToSend): bool
    {
        $dedupeKey = $notifToSend->getDedupeKey();
        if (!is_null($dedupeKey) && $dedupeKey !== '') {
            $qb = $this->entityManager->createQueryBuilder();
            $updated = $qb
                ->update(NotifToSend::class, 'n')
                ->set('n.status', ':sending')
                ->andWhere('n.dedupeKey = :dedupeKey')
                ->andWhere('n.status = :pending')
                ->setParameter('sending', 'sending')
                ->setParameter('pending', 'pending')
                ->setParameter('dedupeKey', $dedupeKey)
                ->getQuery()
                ->execute();

            return $updated >= 1;
        }

        $user = $notifToSend->getUser();
        $sendAt = $notifToSend->getSendAt();
        $type = $notifToSend->getType();
        if (is_null($user) || is_null($sendAt) || is_null($type)) {
            return $this->claimNotif($notifToSend);
        }

        $qb = $this->entityManager->createQueryBuilder();
        $updated = $qb
            ->update(NotifToSend::class, 'n')
            ->set('n.status', ':sending')
            ->andWhere('n.user = :user')
            ->andWhere('n.view = :view')
            ->andWhere('n.type = :type')
            ->andWhere('n.sendAt = :sendAt')
            ->andWhere('n.status = :pending')
            ->setParameter('sending', 'sending')
            ->setParameter('pending', 'pending')
            ->setParameter('user', $user)
            ->setParameter('view', 'pray')
            ->setParameter('type', $type)
            ->setParameter('sendAt', $sendAt)
            ->getQuery()
            ->execute();

        return $updated >= 1;
    }

    private function cleanupPrayNotifGroup(NotifToSend $notifToSend): void
    {
        $dedupeKey = $notifToSend->getDedupeKey();
        if (!is_null($dedupeKey) && $dedupeKey !== '') {
            $qb = $this->entityManager->createQueryBuilder();
            $qb->delete(NotifToSend::class, 'n')
                ->andWhere('n.dedupeKey = :dedupeKey')
                ->setParameter('dedupeKey', $dedupeKey)
                ->getQuery()
                ->execute();
            return;
        }

        $user = $notifToSend->getUser();
        $sendAt = $notifToSend->getSendAt();
        $type = $notifToSend->getType();
        if (is_null($user) || is_null($sendAt) || is_null($type)) {
            $this->entityManager->remove($notifToSend);
            return;
        }

        $qb = $this->entityManager->createQueryBuilder();
        $qb->delete(NotifToSend::class, 'n')
            ->andWhere('n.user = :user')
            ->andWhere('n.view = :view')
            ->andWhere('n.type = :type')
            ->andWhere('n.sendAt = :sendAt')
            ->setParameter('user', $user)
            ->setParameter('view', 'pray')
            ->setParameter('type', $type)
            ->setParameter('sendAt', $sendAt)
            ->getQuery()
            ->execute();
    }
}
