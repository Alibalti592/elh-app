<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251011130440 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tranche (id INT AUTO_INCREMENT NOT NULL, obligation_id INT NOT NULL, emprunteur_id INT NOT NULL, amount NUMERIC(10, 2) NOT NULL, paid_at DATETIME NOT NULL, status VARCHAR(50) NOT NULL, file_url VARCHAR(255) DEFAULT NULL, INDEX IDX_66675840DFA60A57 (obligation_id), INDEX IDX_66675840F0840037 (emprunteur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tranche ADD CONSTRAINT FK_66675840DFA60A57 FOREIGN KEY (obligation_id) REFERENCES obligation (id)');
        $this->addSql('ALTER TABLE tranche ADD CONSTRAINT FK_66675840F0840037 FOREIGN KEY (emprunteur_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE notif_to_send ADD status VARCHAR(20) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tranche DROP FOREIGN KEY FK_66675840DFA60A57');
        $this->addSql('ALTER TABLE tranche DROP FOREIGN KEY FK_66675840F0840037');
        $this->addSql('DROP TABLE tranche');
        $this->addSql('ALTER TABLE notif_to_send DROP status');
    }
}
