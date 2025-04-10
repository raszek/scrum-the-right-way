<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240919123945 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uniq_8fa30d598a90aba9');
        $this->addSql('ALTER TABLE issue_column DROP key');
        $this->addSql('ALTER TABLE issue_column ALTER id DROP IDENTITY');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE issue_column ADD key VARCHAR(40) NOT NULL');
        $this->addSql('ALTER TABLE issue_column ALTER id ADD GENERATED BY DEFAULT AS IDENTITY');
        $this->addSql('CREATE UNIQUE INDEX uniq_8fa30d598a90aba9 ON issue_column (key)');
    }
}
