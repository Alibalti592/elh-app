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

   // use SymfonyStyle for nice output
use Symfony\Component\Console\Style\SymfonyStyle;
// ...
protected function execute(InputInterface $input, OutputInterface $output) {
    $io = new SymfonyStyle($input, $output);
    $io->section('app:add-pray-notif (debug)');

    $prayNotifs = $this->entityManager->getRepository(PrayNotification::class)->findPrayNotifToAdd();
    $io->writeln('Found PrayNotification to add: '.count($prayNotifs));

    foreach ($prayNotifs as $prayNotif) {
        $prayNotif->setNotifAdded(true);
        $this->entityManager->persist($prayNotif);
    }
    $this->entityManager->flush();

    $created = 0;

    foreach ($prayNotifs as $prayNotif) {
        $currentUser = $prayNotif->getUser();
        $praytimesUI = $this->prayTimesService->getPrayTimesOfDay($currentUser);

        // remove existing
        $toremoves = $this->entityManager->getRepository(NotifToSend::class)->findPrayNotifOfUser($currentUser);
        foreach ($toremoves as $toremove) {
            $this->entityManager->remove($toremove);
        }
        $this->entityManager->flush();

        $io->writeln(sprintf('User %d: %d candidate times', $currentUser->getId(), count($praytimesUI)));

        foreach ($praytimesUI as $row) {
            $sendAt = (new \DateTime())->setTimestamp($row['timestamp']);
            $now = new \DateTime();
            $io->writeln(sprintf(
                ' - %s @ %s (ts=%d) isNotified=%s %s',
                $row['key'], $sendAt->format('Y-m-d H:i:s T'), $row['timestamp'],
                $row['isNotified'] ? 'yes' : 'no',
                $sendAt > $now ? 'FUTURE' : 'PAST'
            ));

            if ($row['isNotified'] && $sendAt > $now) {
                $notifToSend = new NotifToSend();
                $notifToSend->setView('pray');
                $notifToSend->setForPrayFromUI($currentUser, $row);
                $this->entityManager->persist($notifToSend);
                $created++;
            }
        }
        $this->entityManager->flush();
    }

    $io->success("Created $created NotifToSend rows");
    return Command::SUCCESS;
}

}