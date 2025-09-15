<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250913114505 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE suit (id INT AUTO_INCREMENT NOT NULL, updated_by_id INT DEFAULT NULL, deleted_by_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, region VARCHAR(50) NOT NULL, gender VARCHAR(1) NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, INDEX IDX_E9A31F1E896DBBDE (updated_by_id), INDEX IDX_E9A31F1EC76F1F52 (deleted_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE suit_item (suit_id INT NOT NULL, item_id INT NOT NULL, INDEX IDX_C11690B3F27CB76F (suit_id), INDEX IDX_C11690B3126F525E (item_id), PRIMARY KEY(suit_id, item_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE suit_item ADD CONSTRAINT FK_C11690B3F27CB76F FOREIGN KEY (suit_id) REFERENCES suit (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE suit_item ADD CONSTRAINT FK_C11690B3126F525E FOREIGN KEY (item_id) REFERENCES item (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE suit_item DROP FOREIGN KEY FK_C11690B3F27CB76F');
        $this->addSql('ALTER TABLE suit_item DROP FOREIGN KEY FK_C11690B3126F525E');
        $this->addSql('DROP TABLE suit');
        $this->addSql('DROP TABLE suit_item');
    }
}
