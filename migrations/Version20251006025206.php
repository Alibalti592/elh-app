<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251006025206 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notif_to_send ADD tranche_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE notif_to_send ADD CONSTRAINT FK_BC2080E6B76F6B31 FOREIGN KEY (tranche_id) REFERENCES tranche (id)');
        $this->addSql('CREATE INDEX IDX_BC2080E6B76F6B31 ON notif_to_send (tranche_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notif_to_send DROP FOREIGN KEY FK_BC2080E6B76F6B31');
        $this->addSql('DROP INDEX IDX_BC2080E6B76F6B31 ON notif_to_send');
        $this->addSql('ALTER TABLE notif_to_send DROP tranche_id');
    }
}
