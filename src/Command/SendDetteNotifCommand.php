<?php
namespace App\Command;

use App\Entity\Obligation;
use App\Services\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendDetteNotifCommand extends Command
{
    protected static $defaultName = 'app:send-dette-notif';

    private $entityManager;
    public function __construct(EntityManagerInterface $entityManager, private readonly NotificationService $notificationService) {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure() {

    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        set_time_limit ( 3600 );
        $dettes = $this->entityManager->getRepository(Obligation::class)->findObligationsForNotif();
        foreach ($dettes as $obligation) {
            $this->notificationService->notifForObligationEchance($obligation);
        }
        return Command::SUCCESS;
    }
}