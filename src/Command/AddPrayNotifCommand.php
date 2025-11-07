<?php
namespace App\Command;

use App\Entity\NotifToSend;
use App\Entity\PrayNotification;
use App\Services\PrayTimesService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AddPrayNotifCommand extends Command
{
    protected static $defaultName = 'app:add-pray-notif';

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PrayTimesService $prayTimesService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->section('app:add-pray-notif (stateless)');

        // App timezone (you’ve standardized on Etc/GMT-1 = UTC+1)
        $tz = new \DateTimeZone('Etc/GMT-1');
        $now = new \DateTimeImmutable('now', $tz);
        $startOfDay = new \DateTimeImmutable($now->format('Y-m-d') . ' 00:00:00', $tz);

        /** @var PrayNotification[] $all */
        $all = $this->em->getRepository(PrayNotification::class)->findAll();
        $io->writeln('Users with PrayNotification: ' . count($all));

        $created = 0;

        foreach ($all as $pn) {
            $user = $pn->getUser();

            // 1) Delete TODAY + FUTURE queued 'pray' notifs for this user (stateless rebuild)
            // If NotifToSend has a user relation and a DateTime field sendAt:
            $this->em->createQuery(
                'DELETE FROM App\Entity\NotifToSend n
                 WHERE n.view = :view AND n.user = :user AND n.sendAt >= :start'
            )->setParameters([
                'view'  => 'pray',
                'user'  => $user,
                'start' => $startOfDay,
            ])->execute();

            // 2) Recompute from PrayTimesService (already returns today’s times)
            $ui = $this->prayTimesService->getPrayTimesOfDay($user);

            $io->writeln(sprintf('User %d: %d candidate times', $user->getId(), count($ui)));

            foreach ($ui as $row) {
                // Only if the user enabled that prayer
                if (empty($row['isNotified'])) {
                    continue;
                }

                // Timestamp -> DateTime in our app TZ
                $sendAt = (new \DateTimeImmutable('@' . $row['timestamp']))->setTimezone($tz);

                $io->writeln(sprintf(
                    ' - %s @ %s (ts=%d) isNotified=%s %s',
                    $row['key'],
                    $sendAt->format('Y-m-d H:i:s T'),
                    $row['timestamp'],
                    $row['isNotified'] ? 'yes' : 'no',
                    $sendAt > $now ? 'FUTURE' : 'PAST'
                ));

                // Queue only future items
                if ($sendAt <= $now) {
                    continue;
                }

                $n = new NotifToSend();
                $n->setView('pray');
                // setForPrayFromUI should set user, payload, sendAt, title/body…
                $n->setForPrayFromUI($user, $row);
                $this->em->persist($n);
                $created++;
            }

            $this->em->flush();
        }

        $io->success("Created $created NotifToSend rows (stateless)");
        return Command::SUCCESS;
    }
}
