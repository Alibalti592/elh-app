<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260129090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add dedupe_key to notif_to_send to prevent duplicate pray notifications';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE notif_to_send ADD dedupe_key VARCHAR(191) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_NOTIF_TO_SEND_DEDUPE_KEY ON notif_to_send (dedupe_key)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_NOTIF_TO_SEND_DEDUPE_KEY ON notif_to_send');
        $this->addSql('ALTER TABLE notif_to_send DROP dedupe_key');
    }
}
