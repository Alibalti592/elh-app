<?php
namespace App\Command;

use App\Entity\NotifToSend;
use App\Services\FcmNotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

//remise à 0 pour envoi le lendemain
// => éxecuter la nuit 1 seule fois et éviter que l'autre tache d'envoi puisse s'éxecuter en même temps !!!
class CleanPrayNotifCommand extends Command
{
    protected static $defaultName = 'app:clean-pray-notif';

    private $entityManager;
    public function __construct(EntityManagerInterface $entityManager) {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure() {

    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $conn = $this->entityManager->getConnection();
        $sql = "UPDATE `pray_notification` SET `notif_added`=0 WHERE 1";
        $stmt = $conn->prepare($sql);
        $stmt->executeQuery();
        return Command::SUCCESS;
    }
}