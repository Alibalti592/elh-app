<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260215160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create user_feedback table to store comments from app users';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE user_feedback (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, comment LONGTEXT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_2082B0E7A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_feedback ADD CONSTRAINT FK_2082B0E7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE user_feedback');
    }
}
