<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251109222812 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX message_scheduled_idx ON message (scheduled_at)');
        $this->addSql('CREATE INDEX message_processed_idx ON message (processed_at)');
        $this->addSql('CREATE INDEX message_status_idx ON message (status)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX message_scheduled_idx ON message');
        $this->addSql('DROP INDEX message_processed_idx ON message');
        $this->addSql('DROP INDEX message_status_idx ON message');
    }
}
