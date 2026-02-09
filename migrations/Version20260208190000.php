<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260208190000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add years JSON field to jeun for multi-year tracking';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE jeun ADD years JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE jeun DROP years');
    }
}
