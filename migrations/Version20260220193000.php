<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260220193000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create mobile_app_version table for update checks on iOS/Android';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE mobile_app_version (id INT AUTO_INCREMENT NOT NULL, platform VARCHAR(20) NOT NULL, version VARCHAR(30) NOT NULL, UNIQUE INDEX uniq_mobile_app_version_platform (platform), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql("INSERT INTO mobile_app_version (platform, version) VALUES ('ios', '1.1.3'), ('android', '1.1.3')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE mobile_app_version');
    }
}

