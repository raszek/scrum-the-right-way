<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240820085046 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uniq_12ad233e166d1f9cff5d5c9f62a6dc27');
        $this->addSql('ALTER TABLE issue RENAME COLUMN priority TO column_position');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_12AD233E166D1F9CFF5D5C9FACF2D824 ON issue (project_id, issue_column_id, column_position)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX UNIQ_12AD233E166D1F9CFF5D5C9FACF2D824');
        $this->addSql('ALTER TABLE issue RENAME COLUMN column_position TO priority');
        $this->addSql('CREATE UNIQUE INDEX uniq_12ad233e166d1f9cff5d5c9f62a6dc27 ON issue (project_id, issue_column_id, priority)');
    }
}
