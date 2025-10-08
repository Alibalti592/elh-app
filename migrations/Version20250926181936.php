<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250926181936 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE carte (id INT AUTO_INCREMENT NOT NULL, created_by_id INT NOT NULL, salat_id INT DEFAULT NULL, afiliation VARCHAR(255) NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, death_date DATETIME DEFAULT NULL, content LONGTEXT DEFAULT NULL, type VARCHAR(255) NOT NULL, location_name VARCHAR(255) DEFAULT NULL, onmyname VARCHAR(25) NOT NULL, phone VARCHAR(50) DEFAULT NULL, phone_prefix VARCHAR(6) DEFAULT NULL, INDEX IDX_BAD4FFFDB03A8386 (created_by_id), UNIQUE INDEX UNIQ_BAD4FFFDCC05B47E (salat_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE carte_share (id INT AUTO_INCREMENT NOT NULL, carte_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_67A9E985C9C7CEB6 (carte_id), INDEX IDX_67A9E985A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE carte_text (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, for_other TINYINT(1) NOT NULL, content LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE chat_message (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, chat_thread_id INT NOT NULL, file_id INT DEFAULT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_FAB3FC16B03A8386 (created_by_id), INDEX IDX_FAB3FC16C47D5262 (chat_thread_id), UNIQUE INDEX UNIQ_FAB3FC1693CB796C (file_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE chat_notification (id INT AUTO_INCREMENT NOT NULL, thread_id INT NOT NULL, user_id INT NOT NULL, updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_41BF1F4BE2904019 (thread_id), INDEX IDX_41BF1F4BA76ED395 (user_id), UNIQUE INDEX unique_relation (user_id, thread_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE chat_participant (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, thread_id INT NOT NULL, INDEX IDX_E8ED9C89A76ED395 (user_id), INDEX IDX_E8ED9C89E2904019 (thread_id), UNIQUE INDEX unique_relation (user_id, thread_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE chat_thread (id INT AUTO_INCREMENT NOT NULL, image_id INT DEFAULT NULL, last_message_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, type VARCHAR(20) NOT NULL, last_update DATETIME NOT NULL, name VARCHAR(255) DEFAULT NULL, reference VARCHAR(255) DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_56FC7BA23DA5256D (image_id), UNIQUE INDEX UNIQ_56FC7BA2BA0E79C3 (last_message_id), INDEX IDX_56FC7BA2B03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dece (id INT AUTO_INCREMENT NOT NULL, location_id INT DEFAULT NULL, created_by_id INT NOT NULL, afiliation VARCHAR(255) NOT NULL, lieu VARCHAR(255) NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, date DATETIME NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', notif_pf TINYINT(1) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_A8141B3064D218E (location_id), INDEX IDX_A8141B30B03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE deuil (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE deuil_date (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, end_date DATETIME NOT NULL, ref VARCHAR(255) NOT NULL, INDEX IDX_9943E108A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE don (id INT AUTO_INCREMENT NOT NULL, image_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, link VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_F8F081D93DA5256D (image_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE faq (id INT AUTO_INCREMENT NOT NULL, question LONGTEXT NOT NULL, reponse LONGTEXT NOT NULL, online TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fcm_token (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, fcmToken VARCHAR(255) NOT NULL, device_id VARCHAR(255) DEFAULT NULL, INDEX IDX_19B88AF9A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE imam (id INT AUTO_INCREMENT NOT NULL, location_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, online TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_688078E564D218E (location_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE intro (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT NOT NULL, page VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE invitation (id INT AUTO_INCREMENT NOT NULL, created_by_id INT NOT NULL, email VARCHAR(255) NOT NULL, accpeted TINYINT(1) NOT NULL, INDEX IDX_F11D61A2B03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE jeun (id INT AUTO_INCREMENT NOT NULL, created_by_id INT NOT NULL, nb_days SMALLINT NOT NULL, text LONGTEXT DEFAULT NULL, selected_year SMALLINT NOT NULL, jeun_nb_days_r SMALLINT NOT NULL, UNIQUE INDEX UNIQ_C381005CB03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE location (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, lat DOUBLE PRECISION NOT NULL, lng DOUBLE PRECISION NOT NULL, city VARCHAR(255) NOT NULL, post_code VARCHAR(255) NOT NULL, region VARCHAR(255) NOT NULL, adress VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mail (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, mailkey VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, subject VARCHAR(255) NOT NULL, variables VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE maraude (id INT AUTO_INCREMENT NOT NULL, location_id INT NOT NULL, managed_by_id INT DEFAULT NULL, date DATETIME NOT NULL, online TINYINT(1) NOT NULL, validated TINYINT(1) NOT NULL, description LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_DA5E9CA264D218E (location_id), INDEX IDX_DA5E9CA2873649CA (managed_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE media (id INT AUTO_INCREMENT NOT NULL, bucket VARCHAR(255) NOT NULL, filename VARCHAR(255) NOT NULL, type VARCHAR(255) DEFAULT NULL, onS3 TINYINT(1) NOT NULL, folder VARCHAR(255) DEFAULT NULL, version VARCHAR(20) DEFAULT NULL, ordered INT DEFAULT NULL, label VARCHAR(255) DEFAULT NULL, size_prefixes VARCHAR(255) DEFAULT NULL, file_size DOUBLE PRECISION DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mosque (id INT AUTO_INCREMENT NOT NULL, location_id INT NOT NULL, managed_by_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, online TINYINT(1) NOT NULL, tel VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_5DE348CA64D218E (location_id), INDEX IDX_5DE348CA873649CA (managed_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mosque_favorite (id INT AUTO_INCREMENT NOT NULL, mosque_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_13CC3C94FBDAA034 (mosque_id), INDEX IDX_13CC3C94A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mosque_notif_dece (id INT AUTO_INCREMENT NOT NULL, mosque_id INT NOT NULL, dece_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', show_on_page TINYINT(1) NOT NULL, INDEX IDX_144C6C0CFBDAA034 (mosque_id), INDEX IDX_144C6C0CF1C63FEF (dece_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE nav_page_content (id INT AUTO_INCREMENT NOT NULL, image_id INT DEFAULT NULL, slug VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT DEFAULT NULL, video VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_4D9AA9963DA5256D (image_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notif_to_send (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, message LONGTEXT NOT NULL, title VARCHAR(255) NOT NULL, datas LONGTEXT DEFAULT NULL, send_at DATETIME NOT NULL, type VARCHAR(255) NOT NULL, view VARCHAR(20) DEFAULT NULL, INDEX IDX_BC2080E6A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE obligation (id INT AUTO_INCREMENT NOT NULL, created_by_id INT NOT NULL, related_to_id INT DEFAULT NULL, remaining_amount NUMERIC(10, 2) NOT NULL, firstname VARCHAR(255) DEFAULT NULL, lastname VARCHAR(255) DEFAULT NULL, adress LONGTEXT DEFAULT NULL, tel VARCHAR(255) DEFAULT NULL, amount VARCHAR(255) NOT NULL, date DATETIME DEFAULT NULL, raison LONGTEXT DEFAULT NULL, delay VARCHAR(255) DEFAULT NULL, conditon_type VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, moyen VARCHAR(255) DEFAULT NULL, date_start DATETIME DEFAULT NULL, status VARCHAR(255) DEFAULT NULL, deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_720EBF27B03A8386 (created_by_id), INDEX IDX_720EBF2740B4AC4E (related_to_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE page (id INT AUTO_INCREMENT NOT NULL, slug VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, title VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pardon (id INT AUTO_INCREMENT NOT NULL, created_by_id INT NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_D835C243B03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pardon_share (id INT AUTO_INCREMENT NOT NULL, pardon_id INT NOT NULL, share_with_id INT NOT NULL, INDEX IDX_3B48DEA4BBE2879E (pardon_id), INDEX IDX_3B48DEA4B2F44014 (share_with_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pompe (id INT AUTO_INCREMENT NOT NULL, location_id INT NOT NULL, managed_by_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', description LONGTEXT DEFAULT NULL, online TINYINT(1) NOT NULL, validated TINYINT(1) NOT NULL, phone VARCHAR(255) DEFAULT NULL, fullname VARCHAR(255) DEFAULT NULL, emailpro VARCHAR(255) DEFAULT NULL, phone_prefix VARCHAR(10) DEFAULT NULL, phone_urgence VARCHAR(255) DEFAULT NULL, prefix_urgence VARCHAR(10) DEFAULT NULL, UNIQUE INDEX UNIQ_E5D44D564D218E (location_id), INDEX IDX_E5D44D5873649CA (managed_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pompe_notification (id INT AUTO_INCREMENT NOT NULL, pompe_id INT NOT NULL, dece_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', accepted TINYINT(1) NOT NULL, status VARCHAR(255) DEFAULT NULL, INDEX IDX_CF4699096CCC95AD (pompe_id), INDEX IDX_CF469909F1C63FEF (dece_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pray_notification (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, prays VARCHAR(255) NOT NULL, notif_added TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_A01A295BA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE refresh_tokens (id INT AUTO_INCREMENT NOT NULL, refresh_token VARCHAR(128) NOT NULL, username VARCHAR(255) NOT NULL, valid DATETIME NOT NULL, UNIQUE INDEX UNIQ_9BACE7E1C74F2195 (refresh_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE relation (id INT AUTO_INCREMENT NOT NULL, user_source_id INT NOT NULL, user_target_id INT NOT NULL, type VARCHAR(255) DEFAULT NULL, status VARCHAR(60) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_6289474995DC9185 (user_source_id), INDEX IDX_62894749156E8682 (user_target_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE resetpassword (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, code VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expire_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_C88C64F6A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE salat (id INT AUTO_INCREMENT NOT NULL, created_by_id INT NOT NULL, mosque_id INT DEFAULT NULL, location_id INT DEFAULT NULL, afiliation VARCHAR(255) NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, ceremony_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', mosque_name VARCHAR(255) DEFAULT NULL, content LONGTEXT DEFAULT NULL, cimetary VARCHAR(255) NOT NULL, INDEX IDX_918920E3B03A8386 (created_by_id), INDEX IDX_918920E3FBDAA034 (mosque_id), UNIQUE INDEX UNIQ_918920E364D218E (location_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE salat_share (id INT AUTO_INCREMENT NOT NULL, salat_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_58AF1C98CC05B47E (salat_id), INDEX IDX_58AF1C98A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE testament (id INT AUTO_INCREMENT NOT NULL, created_by_id INT NOT NULL, location LONGTEXT DEFAULT NULL, family LONGTEXT DEFAULT NULL, goods LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', update_at DATETIME NOT NULL, toilette LONGTEXT DEFAULT NULL, fixe LONGTEXT DEFAULT NULL, lastwill LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_116A262DB03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE testament_share (id INT AUTO_INCREMENT NOT NULL, testament_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_D9F263AA386D1BF0 (testament_id), INDEX IDX_D9F263AAA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE todo (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT NOT NULL, ordered INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tranche (id INT AUTO_INCREMENT NOT NULL, obligation_id INT NOT NULL, emprunteur_id INT NOT NULL, amount NUMERIC(10, 2) NOT NULL, paid_at DATETIME NOT NULL, status VARCHAR(50) NOT NULL, INDEX IDX_66675840DFA60A57 (obligation_id), INDEX IDX_66675840F0840037 (emprunteur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, location_id INT DEFAULT NULL, photo_id INT DEFAULT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, create_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', last_login DATETIME DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, show_dette_infos TINYINT(1) DEFAULT NULL, phone_prefix VARCHAR(255) NOT NULL, enabled TINYINT(1) NOT NULL, deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), UNIQUE INDEX UNIQ_8D93D64964D218E (location_id), UNIQUE INDEX UNIQ_8D93D6497E9E4C8C (photo_id), FULLTEXT INDEX IDX_8D93D64983A00E683124B5B6 (firstname, lastname), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE carte ADD CONSTRAINT FK_BAD4FFFDB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE carte ADD CONSTRAINT FK_BAD4FFFDCC05B47E FOREIGN KEY (salat_id) REFERENCES salat (id)');
        $this->addSql('ALTER TABLE carte_share ADD CONSTRAINT FK_67A9E985C9C7CEB6 FOREIGN KEY (carte_id) REFERENCES carte (id)');
        $this->addSql('ALTER TABLE carte_share ADD CONSTRAINT FK_67A9E985A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE chat_message ADD CONSTRAINT FK_FAB3FC16B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE chat_message ADD CONSTRAINT FK_FAB3FC16C47D5262 FOREIGN KEY (chat_thread_id) REFERENCES chat_thread (id)');
        $this->addSql('ALTER TABLE chat_message ADD CONSTRAINT FK_FAB3FC1693CB796C FOREIGN KEY (file_id) REFERENCES media (id)');
        $this->addSql('ALTER TABLE chat_notification ADD CONSTRAINT FK_41BF1F4BE2904019 FOREIGN KEY (thread_id) REFERENCES chat_thread (id)');
        $this->addSql('ALTER TABLE chat_notification ADD CONSTRAINT FK_41BF1F4BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE chat_participant ADD CONSTRAINT FK_E8ED9C89A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE chat_participant ADD CONSTRAINT FK_E8ED9C89E2904019 FOREIGN KEY (thread_id) REFERENCES chat_thread (id)');
        $this->addSql('ALTER TABLE chat_thread ADD CONSTRAINT FK_56FC7BA23DA5256D FOREIGN KEY (image_id) REFERENCES media (id)');
        $this->addSql('ALTER TABLE chat_thread ADD CONSTRAINT FK_56FC7BA2BA0E79C3 FOREIGN KEY (last_message_id) REFERENCES chat_message (id)');
        $this->addSql('ALTER TABLE chat_thread ADD CONSTRAINT FK_56FC7BA2B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE dece ADD CONSTRAINT FK_A8141B3064D218E FOREIGN KEY (location_id) REFERENCES location (id)');
        $this->addSql('ALTER TABLE dece ADD CONSTRAINT FK_A8141B30B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE deuil_date ADD CONSTRAINT FK_9943E108A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE don ADD CONSTRAINT FK_F8F081D93DA5256D FOREIGN KEY (image_id) REFERENCES media (id)');
        $this->addSql('ALTER TABLE fcm_token ADD CONSTRAINT FK_19B88AF9A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE imam ADD CONSTRAINT FK_688078E564D218E FOREIGN KEY (location_id) REFERENCES location (id)');
        $this->addSql('ALTER TABLE invitation ADD CONSTRAINT FK_F11D61A2B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE jeun ADD CONSTRAINT FK_C381005CB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE maraude ADD CONSTRAINT FK_DA5E9CA264D218E FOREIGN KEY (location_id) REFERENCES location (id)');
        $this->addSql('ALTER TABLE maraude ADD CONSTRAINT FK_DA5E9CA2873649CA FOREIGN KEY (managed_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE mosque ADD CONSTRAINT FK_5DE348CA64D218E FOREIGN KEY (location_id) REFERENCES location (id)');
        $this->addSql('ALTER TABLE mosque ADD CONSTRAINT FK_5DE348CA873649CA FOREIGN KEY (managed_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE mosque_favorite ADD CONSTRAINT FK_13CC3C94FBDAA034 FOREIGN KEY (mosque_id) REFERENCES mosque (id)');
        $this->addSql('ALTER TABLE mosque_favorite ADD CONSTRAINT FK_13CC3C94A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE mosque_notif_dece ADD CONSTRAINT FK_144C6C0CFBDAA034 FOREIGN KEY (mosque_id) REFERENCES mosque (id)');
        $this->addSql('ALTER TABLE mosque_notif_dece ADD CONSTRAINT FK_144C6C0CF1C63FEF FOREIGN KEY (dece_id) REFERENCES dece (id)');
        $this->addSql('ALTER TABLE nav_page_content ADD CONSTRAINT FK_4D9AA9963DA5256D FOREIGN KEY (image_id) REFERENCES media (id)');
        $this->addSql('ALTER TABLE notif_to_send ADD CONSTRAINT FK_BC2080E6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE obligation ADD CONSTRAINT FK_720EBF27B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE obligation ADD CONSTRAINT FK_720EBF2740B4AC4E FOREIGN KEY (related_to_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE pardon ADD CONSTRAINT FK_D835C243B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE pardon_share ADD CONSTRAINT FK_3B48DEA4BBE2879E FOREIGN KEY (pardon_id) REFERENCES pardon (id)');
        $this->addSql('ALTER TABLE pardon_share ADD CONSTRAINT FK_3B48DEA4B2F44014 FOREIGN KEY (share_with_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE pompe ADD CONSTRAINT FK_E5D44D564D218E FOREIGN KEY (location_id) REFERENCES location (id)');
        $this->addSql('ALTER TABLE pompe ADD CONSTRAINT FK_E5D44D5873649CA FOREIGN KEY (managed_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE pompe_notification ADD CONSTRAINT FK_CF4699096CCC95AD FOREIGN KEY (pompe_id) REFERENCES pompe (id)');
        $this->addSql('ALTER TABLE pompe_notification ADD CONSTRAINT FK_CF469909F1C63FEF FOREIGN KEY (dece_id) REFERENCES dece (id)');
        $this->addSql('ALTER TABLE pray_notification ADD CONSTRAINT FK_A01A295BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE relation ADD CONSTRAINT FK_6289474995DC9185 FOREIGN KEY (user_source_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE relation ADD CONSTRAINT FK_62894749156E8682 FOREIGN KEY (user_target_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE resetpassword ADD CONSTRAINT FK_C88C64F6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE salat ADD CONSTRAINT FK_918920E3B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE salat ADD CONSTRAINT FK_918920E3FBDAA034 FOREIGN KEY (mosque_id) REFERENCES mosque (id)');
        $this->addSql('ALTER TABLE salat ADD CONSTRAINT FK_918920E364D218E FOREIGN KEY (location_id) REFERENCES location (id)');
        $this->addSql('ALTER TABLE salat_share ADD CONSTRAINT FK_58AF1C98CC05B47E FOREIGN KEY (salat_id) REFERENCES salat (id)');
        $this->addSql('ALTER TABLE salat_share ADD CONSTRAINT FK_58AF1C98A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE testament ADD CONSTRAINT FK_116A262DB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE testament_share ADD CONSTRAINT FK_D9F263AA386D1BF0 FOREIGN KEY (testament_id) REFERENCES testament (id)');
        $this->addSql('ALTER TABLE testament_share ADD CONSTRAINT FK_D9F263AAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE tranche ADD CONSTRAINT FK_66675840DFA60A57 FOREIGN KEY (obligation_id) REFERENCES obligation (id)');
        $this->addSql('ALTER TABLE tranche ADD CONSTRAINT FK_66675840F0840037 FOREIGN KEY (emprunteur_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64964D218E FOREIGN KEY (location_id) REFERENCES location (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6497E9E4C8C FOREIGN KEY (photo_id) REFERENCES media (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE carte DROP FOREIGN KEY FK_BAD4FFFDB03A8386');
        $this->addSql('ALTER TABLE carte DROP FOREIGN KEY FK_BAD4FFFDCC05B47E');
        $this->addSql('ALTER TABLE carte_share DROP FOREIGN KEY FK_67A9E985C9C7CEB6');
        $this->addSql('ALTER TABLE carte_share DROP FOREIGN KEY FK_67A9E985A76ED395');
        $this->addSql('ALTER TABLE chat_message DROP FOREIGN KEY FK_FAB3FC16B03A8386');
        $this->addSql('ALTER TABLE chat_message DROP FOREIGN KEY FK_FAB3FC16C47D5262');
        $this->addSql('ALTER TABLE chat_message DROP FOREIGN KEY FK_FAB3FC1693CB796C');
        $this->addSql('ALTER TABLE chat_notification DROP FOREIGN KEY FK_41BF1F4BE2904019');
        $this->addSql('ALTER TABLE chat_notification DROP FOREIGN KEY FK_41BF1F4BA76ED395');
        $this->addSql('ALTER TABLE chat_participant DROP FOREIGN KEY FK_E8ED9C89A76ED395');
        $this->addSql('ALTER TABLE chat_participant DROP FOREIGN KEY FK_E8ED9C89E2904019');
        $this->addSql('ALTER TABLE chat_thread DROP FOREIGN KEY FK_56FC7BA23DA5256D');
        $this->addSql('ALTER TABLE chat_thread DROP FOREIGN KEY FK_56FC7BA2BA0E79C3');
        $this->addSql('ALTER TABLE chat_thread DROP FOREIGN KEY FK_56FC7BA2B03A8386');
        $this->addSql('ALTER TABLE dece DROP FOREIGN KEY FK_A8141B3064D218E');
        $this->addSql('ALTER TABLE dece DROP FOREIGN KEY FK_A8141B30B03A8386');
        $this->addSql('ALTER TABLE deuil_date DROP FOREIGN KEY FK_9943E108A76ED395');
        $this->addSql('ALTER TABLE don DROP FOREIGN KEY FK_F8F081D93DA5256D');
        $this->addSql('ALTER TABLE fcm_token DROP FOREIGN KEY FK_19B88AF9A76ED395');
        $this->addSql('ALTER TABLE imam DROP FOREIGN KEY FK_688078E564D218E');
        $this->addSql('ALTER TABLE invitation DROP FOREIGN KEY FK_F11D61A2B03A8386');
        $this->addSql('ALTER TABLE jeun DROP FOREIGN KEY FK_C381005CB03A8386');
        $this->addSql('ALTER TABLE maraude DROP FOREIGN KEY FK_DA5E9CA264D218E');
        $this->addSql('ALTER TABLE maraude DROP FOREIGN KEY FK_DA5E9CA2873649CA');
        $this->addSql('ALTER TABLE mosque DROP FOREIGN KEY FK_5DE348CA64D218E');
        $this->addSql('ALTER TABLE mosque DROP FOREIGN KEY FK_5DE348CA873649CA');
        $this->addSql('ALTER TABLE mosque_favorite DROP FOREIGN KEY FK_13CC3C94FBDAA034');
        $this->addSql('ALTER TABLE mosque_favorite DROP FOREIGN KEY FK_13CC3C94A76ED395');
        $this->addSql('ALTER TABLE mosque_notif_dece DROP FOREIGN KEY FK_144C6C0CFBDAA034');
        $this->addSql('ALTER TABLE mosque_notif_dece DROP FOREIGN KEY FK_144C6C0CF1C63FEF');
        $this->addSql('ALTER TABLE nav_page_content DROP FOREIGN KEY FK_4D9AA9963DA5256D');
        $this->addSql('ALTER TABLE notif_to_send DROP FOREIGN KEY FK_BC2080E6A76ED395');
        $this->addSql('ALTER TABLE obligation DROP FOREIGN KEY FK_720EBF27B03A8386');
        $this->addSql('ALTER TABLE obligation DROP FOREIGN KEY FK_720EBF2740B4AC4E');
        $this->addSql('ALTER TABLE pardon DROP FOREIGN KEY FK_D835C243B03A8386');
        $this->addSql('ALTER TABLE pardon_share DROP FOREIGN KEY FK_3B48DEA4BBE2879E');
        $this->addSql('ALTER TABLE pardon_share DROP FOREIGN KEY FK_3B48DEA4B2F44014');
        $this->addSql('ALTER TABLE pompe DROP FOREIGN KEY FK_E5D44D564D218E');
        $this->addSql('ALTER TABLE pompe DROP FOREIGN KEY FK_E5D44D5873649CA');
        $this->addSql('ALTER TABLE pompe_notification DROP FOREIGN KEY FK_CF4699096CCC95AD');
        $this->addSql('ALTER TABLE pompe_notification DROP FOREIGN KEY FK_CF469909F1C63FEF');
        $this->addSql('ALTER TABLE pray_notification DROP FOREIGN KEY FK_A01A295BA76ED395');
        $this->addSql('ALTER TABLE relation DROP FOREIGN KEY FK_6289474995DC9185');
        $this->addSql('ALTER TABLE relation DROP FOREIGN KEY FK_62894749156E8682');
        $this->addSql('ALTER TABLE resetpassword DROP FOREIGN KEY FK_C88C64F6A76ED395');
        $this->addSql('ALTER TABLE salat DROP FOREIGN KEY FK_918920E3B03A8386');
        $this->addSql('ALTER TABLE salat DROP FOREIGN KEY FK_918920E3FBDAA034');
        $this->addSql('ALTER TABLE salat DROP FOREIGN KEY FK_918920E364D218E');
        $this->addSql('ALTER TABLE salat_share DROP FOREIGN KEY FK_58AF1C98CC05B47E');
        $this->addSql('ALTER TABLE salat_share DROP FOREIGN KEY FK_58AF1C98A76ED395');
        $this->addSql('ALTER TABLE testament DROP FOREIGN KEY FK_116A262DB03A8386');
        $this->addSql('ALTER TABLE testament_share DROP FOREIGN KEY FK_D9F263AA386D1BF0');
        $this->addSql('ALTER TABLE testament_share DROP FOREIGN KEY FK_D9F263AAA76ED395');
        $this->addSql('ALTER TABLE tranche DROP FOREIGN KEY FK_66675840DFA60A57');
        $this->addSql('ALTER TABLE tranche DROP FOREIGN KEY FK_66675840F0840037');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64964D218E');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6497E9E4C8C');
        $this->addSql('DROP TABLE carte');
        $this->addSql('DROP TABLE carte_share');
        $this->addSql('DROP TABLE carte_text');
        $this->addSql('DROP TABLE chat_message');
        $this->addSql('DROP TABLE chat_notification');
        $this->addSql('DROP TABLE chat_participant');
        $this->addSql('DROP TABLE chat_thread');
        $this->addSql('DROP TABLE dece');
        $this->addSql('DROP TABLE deuil');
        $this->addSql('DROP TABLE deuil_date');
        $this->addSql('DROP TABLE don');
        $this->addSql('DROP TABLE faq');
        $this->addSql('DROP TABLE fcm_token');
        $this->addSql('DROP TABLE imam');
        $this->addSql('DROP TABLE intro');
        $this->addSql('DROP TABLE invitation');
        $this->addSql('DROP TABLE jeun');
        $this->addSql('DROP TABLE location');
        $this->addSql('DROP TABLE mail');
        $this->addSql('DROP TABLE maraude');
        $this->addSql('DROP TABLE media');
        $this->addSql('DROP TABLE mosque');
        $this->addSql('DROP TABLE mosque_favorite');
        $this->addSql('DROP TABLE mosque_notif_dece');
        $this->addSql('DROP TABLE nav_page_content');
        $this->addSql('DROP TABLE notif_to_send');
        $this->addSql('DROP TABLE obligation');
        $this->addSql('DROP TABLE page');
        $this->addSql('DROP TABLE pardon');
        $this->addSql('DROP TABLE pardon_share');
        $this->addSql('DROP TABLE pompe');
        $this->addSql('DROP TABLE pompe_notification');
        $this->addSql('DROP TABLE pray_notification');
        $this->addSql('DROP TABLE refresh_tokens');
        $this->addSql('DROP TABLE relation');
        $this->addSql('DROP TABLE resetpassword');
        $this->addSql('DROP TABLE salat');
        $this->addSql('DROP TABLE salat_share');
        $this->addSql('DROP TABLE testament');
        $this->addSql('DROP TABLE testament_share');
        $this->addSql('DROP TABLE todo');
        $this->addSql('DROP TABLE tranche');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
