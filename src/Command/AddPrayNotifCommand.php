<?php
namespace App\Command;

use App\Entity\NotifToSend;
use App\Entity\PrayNotification;
use App\Services\PrayTimesService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Lock\LockFactory;

class AddPrayNotifCommand extends Command
{
    use LockableTrait;

    protected static $defaultName = 'app:add-pray-notif';

    private $entityManager;
    public function __construct(
        EntityManagerInterface $entityManager,
        private readonly PrayTimesService $prayTimesService,
        private readonly LockFactory $lockFactory
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure() {

    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        if (!$this->lock()) {
            return Command::SUCCESS;
        }

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
            $userLock = $this->lockFactory->createLock('pray_notif_user_'.$currentUser->getId(), 30);
            if (!$userLock->acquire()) {
                continue;
            }
            $praytimesUI = $this->prayTimesService->getPrayTimesOfDay($prayNotif->getUser());
            //remove existing NotifToSend
            try {
                $this->entityManager->getRepository(NotifToSend::class)->deletePrayNotifOfUser($currentUser, true);
                foreach ($praytimesUI as $praytimeUI) {
                    if($praytimeUI['isNotified']) {
                        $sendTimestamp = ((int) $praytimeUI['timestamp']) - (60 * 15);
                        if ($sendTimestamp > time()) {
                            $this->queuePrayNotifFromUI($currentUser, $praytimeUI);
                        }
                    }
                }
                $this->entityManager->flush();
            } finally {
                $userLock->release();
            }
        }

        return Command::SUCCESS;
    }

    private function queuePrayNotifFromUI($user, array $praytimeUI): void
    {
        $sendTimestamp = ((int) ($praytimeUI['timestamp'] ?? 0)) - (60 * 15);
        $prayKey = (string) ($praytimeUI['key'] ?? '');
        if ($sendTimestamp <= time() || $prayKey === '') {
            return;
        }

        $dedupeKey = NotifToSend::buildPrayDedupeKey($user, $prayKey, $sendTimestamp);
        $existing = $this->entityManager->getRepository(NotifToSend::class)->findOneBy([
            'dedupeKey' => $dedupeKey,
        ]);
        if (!is_null($existing)) {
            return;
        }

        $notifToSend = new NotifToSend();
        $notifToSend->setForPrayFromUI($user, $praytimeUI);
        $this->entityManager->persist($notifToSend);
    }
}
