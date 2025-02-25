<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241211111125 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uniq_ca0108278a90aba9');
        $this->addSql('ALTER TABLE thread_status DROP key');
        $this->addSql('ALTER TABLE thread_status ALTER id DROP IDENTITY');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE thread_status ADD key VARCHAR(30) NOT NULL');
        $this->addSql('ALTER TABLE thread_status ALTER id ADD GENERATED BY DEFAULT AS IDENTITY');
        $this->addSql('CREATE UNIQUE INDEX uniq_ca0108278a90aba9 ON thread_status (key)');
    }
}
