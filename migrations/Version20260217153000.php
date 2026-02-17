<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260217153000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add auth_provider on user to track account creation provider (email/google/apple)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE user ADD auth_provider VARCHAR(20) NOT NULL DEFAULT 'email'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP auth_provider');
    }
}

