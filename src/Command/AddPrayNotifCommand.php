<?php
namespace App\Command;

use App\Entity\NotifToSend;
use App\Entity\PrayNotification;
use App\Services\PrayTimesService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddPrayNotifCommand extends Command
{
    protected static $defaultName = 'app:add-pray-notif';

    private $entityManager;
    public function __construct(EntityManagerInterface $entityManager, private readonly PrayTimesService $prayTimesService) {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure() {

    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $prayNotifs = $this->entityManager->getRepository(PrayNotification::class)->findPrayNotifToAdd();
        /** @var PrayNotification $prayNotif */
        foreach ($prayNotifs as $prayNotif) {
            $prayNotif->setNotifAdded(true);
            $this->entityManager->persist($prayNotif);
        }
        $this->entityManager->flush();
        /** @var PrayNotification $prayNotif */
        foreach ($prayNotifs as $prayNotif) {
            $currentUser = $prayNotif->getUser();
            $praytimesUI = $this->prayTimesService->getPrayTimesOfDay($prayNotif->getUser());
            //remove existing NotifToSend
            $toremoves = $this->entityManager->getRepository(NotifToSend::class)->findPrayNotifOfUser($currentUser);
            foreach ($toremoves as $toremove) {
                $this->entityManager->remove($toremove);
            }
            $this->entityManager->flush();
            foreach ($praytimesUI as $praytimeUI) {
                if($praytimeUI['isNotified']) {
                    $sendAt = new \DateTime();
                    $sendAt->setTimestamp($praytimeUI['timestamp']);
                    $now = new \DateTime();
                    if ($sendAt > $now) {
                        $notifToSend = new NotifToSend();
                        $notifToSend->setView('pray');
                        $notifToSend->setForPrayFromUI($currentUser, $praytimeUI);
                        $this->entityManager->persist($notifToSend);
                    }
                }
            }
            $this->entityManager->flush();
        }

        return Command::SUCCESS;
    }
}