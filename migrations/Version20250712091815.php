<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250712091815 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event ADD updated_by_id INT NOT NULL, ADD deleted_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7C76F1F52 FOREIGN KEY (deleted_by_id) REFERENCES user (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3BAE0AA7896DBBDE ON event (updated_by_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3BAE0AA7C76F1F52 ON event (deleted_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7896DBBDE');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7C76F1F52');
        $this->addSql('DROP INDEX UNIQ_3BAE0AA7896DBBDE ON event');
        $this->addSql('DROP INDEX UNIQ_3BAE0AA7C76F1F52 ON event');
        $this->addSql('ALTER TABLE event DROP updated_by_id, DROP deleted_by_id');
    }
}
