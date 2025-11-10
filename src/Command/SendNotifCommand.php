<?php
namespace App\Command;

use App\Entity\NotifToSend;
use App\Services\FcmNotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendNotifCommand extends Command
{
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

        $notifsToSend = $this->entityManager->getRepository(NotifToSend::class)->findNotifToSend();

        /** @var NotifToSend $notifToSend */
        foreach ($notifsToSend as $notifToSend) {
            $data = null;

            if (!is_null($notifToSend->getView())) {
                $data = [
                    'view' => $notifToSend->getView(),
                    
                ];

                // 👉 Si la notif est pour une tranche, on ajoute les actions Accepter / Refuser
                if ($notifToSend->getView() === 'tranche_action') {
                    $data['actions'] = [
                        ['id' => 'accepter', 'title' => 'Accepter'],
                        ['id' => 'refuser', 'title' => 'Refuser'],
                    ];
                }
            }

            $this->fcmNotificationService->sendFcmDefaultNotification(
                $notifToSend->getUser(),
                $notifToSend->getTitle(),
                $notifToSend->getMessage(),
                $data
            );

            if($notifsToSend->getType() === 'tranche') {
                $notifToSend->setStatus('sent');
            } else {
                $this->entityManager->remove($notifToSend);
            }

        
            $this->entityManager->flush();
        }

        return Command::SUCCESS;
    }
}
