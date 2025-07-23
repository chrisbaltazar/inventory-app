<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250723185845 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7C76F1F52');
        $this->addSql('DROP INDEX IDX_3BAE0AA7C76F1F52 ON event');
        $this->addSql('ALTER TABLE event DROP deleted_by_id');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649C76F1F52');
        $this->addSql('DROP INDEX UNIQ_8D93D649C76F1F52 ON user');
        $this->addSql('ALTER TABLE user DROP deleted_by_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event ADD deleted_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7C76F1F52 FOREIGN KEY (deleted_by_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_3BAE0AA7C76F1F52 ON event (deleted_by_id)');
        $this->addSql('ALTER TABLE user ADD deleted_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649C76F1F52 FOREIGN KEY (deleted_by_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649C76F1F52 ON user (deleted_by_id)');
    }
}
