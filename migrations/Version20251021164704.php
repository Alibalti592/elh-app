<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251021164704 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE some_table');
        $this->addSql('ALTER TABLE notif_to_send ADD status VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE obligation ADD currency VARCHAR(3) DEFAULT NULL, ADD file_url VARCHAR(255) DEFAULT NULL, CHANGE adress adress VARCHAR(255) DEFAULT NULL, CHANGE conditon_type conditon_type VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE tranche ADD file_url VARCHAR(255) DEFAULT NULL, CHANGE emprunteur_id emprunteur_id INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE some_table (id INT NOT NULL, conditon_type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE notif_to_send DROP status');
        $this->addSql('ALTER TABLE obligation DROP currency, DROP file_url, CHANGE adress adress LONGTEXT DEFAULT NULL, CHANGE conditon_type conditon_type VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE tranche DROP file_url, CHANGE emprunteur_id emprunteur_id INT NOT NULL');
    }
}
