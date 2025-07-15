<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250715212957 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event DROP INDEX UNIQ_3BAE0AA7896DBBDE, ADD INDEX IDX_3BAE0AA7896DBBDE (updated_by_id)');
        $this->addSql('ALTER TABLE event DROP INDEX UNIQ_3BAE0AA7C76F1F52, ADD INDEX IDX_3BAE0AA7C76F1F52 (deleted_by_id)');
        $this->addSql('ALTER TABLE event CHANGE updated_by_id updated_by_id INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event DROP INDEX IDX_3BAE0AA7896DBBDE, ADD UNIQUE INDEX UNIQ_3BAE0AA7896DBBDE (updated_by_id)');
        $this->addSql('ALTER TABLE event DROP INDEX IDX_3BAE0AA7C76F1F52, ADD UNIQUE INDEX UNIQ_3BAE0AA7C76F1F52 (deleted_by_id)');
        $this->addSql('ALTER TABLE event CHANGE updated_by_id updated_by_id INT NOT NULL');
    }
}
