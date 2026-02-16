<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260216170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ensure one global owner per FCM token to avoid cross-account duplicate notifications';
    }

    public function up(Schema $schema): void
    {
        // remove invalid tokens first
        $this->addSql("DELETE FROM fcm_token WHERE fcmToken = '' OR fcmToken IS NULL");
        // keep only the latest row for each token
        $this->addSql('DELETE t1 FROM fcm_token t1 INNER JOIN fcm_token t2 ON t1.fcmToken = t2.fcmToken AND t1.id < t2.id');
        // enforce global uniqueness at DB level
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FCM_TOKEN_FCMTOKEN ON fcm_token (fcmToken)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_FCM_TOKEN_FCMTOKEN ON fcm_token');
    }
}
