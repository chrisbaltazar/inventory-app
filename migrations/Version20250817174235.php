<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250817174235 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, updated_by_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, date DATE NOT NULL, delivery_date DATE DEFAULT NULL, return_date DATE DEFAULT NULL, updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', public TINYINT(1) NOT NULL, INDEX IDX_3BAE0AA7896DBBDE (updated_by_id), INDEX events_return_date_idx (return_date), INDEX events_deleted_idx (deleted_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE inventory (id INT AUTO_INCREMENT NOT NULL, item_id INT NOT NULL, size VARCHAR(20) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, comments VARCHAR(255) DEFAULT NULL, quantity INT NOT NULL, updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX invent_item_idx (item_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE item (id INT AUTO_INCREMENT NOT NULL, updated_by_id INT DEFAULT NULL, region VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_1F1B251E896DBBDE (updated_by_id), INDEX item_region_idx (region), INDEX item_deleted_idx (deleted_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE loan (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, event_id INT NOT NULL, item_id INT NOT NULL, start_date DATETIME NOT NULL, end_date DATETIME DEFAULT NULL, quantity INT NOT NULL, info VARCHAR(255) DEFAULT NULL, comments LONGTEXT DEFAULT NULL, status VARCHAR(10) NOT NULL, INDEX IDX_C5D30D03A76ED395 (user_id), INDEX IDX_C5D30D0371F7E88B (event_id), INDEX IDX_C5D30D03126F525E (item_id), INDEX loan_end_date_idx (end_date), INDEX loan_info_idx (info), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE metadata (id INT AUTO_INCREMENT NOT NULL, item_id INT NOT NULL, name VARCHAR(255) NOT NULL, value VARCHAR(255) NOT NULL, INDEX IDX_4F143414126F525E (item_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, updated_by_id INT DEFAULT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, name VARCHAR(255) DEFAULT NULL, phone VARCHAR(20) DEFAULT NULL, updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), UNIQUE INDEX UNIQ_8D93D649896DBBDE (updated_by_id), INDEX user_deleted_idx (deleted_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE inventory ADD CONSTRAINT FK_B12D4A36126F525E FOREIGN KEY (item_id) REFERENCES item (id)');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251E896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE loan ADD CONSTRAINT FK_C5D30D03A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE loan ADD CONSTRAINT FK_C5D30D0371F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE loan ADD CONSTRAINT FK_C5D30D03126F525E FOREIGN KEY (item_id) REFERENCES item (id)');
        $this->addSql('ALTER TABLE metadata ADD CONSTRAINT FK_4F143414126F525E FOREIGN KEY (item_id) REFERENCES item (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7896DBBDE');
        $this->addSql('ALTER TABLE inventory DROP FOREIGN KEY FK_B12D4A36126F525E');
        $this->addSql('ALTER TABLE item DROP FOREIGN KEY FK_1F1B251E896DBBDE');
        $this->addSql('ALTER TABLE loan DROP FOREIGN KEY FK_C5D30D03A76ED395');
        $this->addSql('ALTER TABLE loan DROP FOREIGN KEY FK_C5D30D0371F7E88B');
        $this->addSql('ALTER TABLE loan DROP FOREIGN KEY FK_C5D30D03126F525E');
        $this->addSql('ALTER TABLE metadata DROP FOREIGN KEY FK_4F143414126F525E');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649896DBBDE');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE inventory');
        $this->addSql('DROP TABLE item');
        $this->addSql('DROP TABLE loan');
        $this->addSql('DROP TABLE metadata');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
