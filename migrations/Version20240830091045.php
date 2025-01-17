<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240830091045 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project_member_role DROP CONSTRAINT FK_632E51CD64AB9629');
        $this->addSql('ALTER TABLE project_member_role ADD CONSTRAINT FK_632E51CD64AB9629 FOREIGN KEY (project_member_id) REFERENCES project_member (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE project_member_role DROP CONSTRAINT fk_632e51cd64ab9629');
        $this->addSql('ALTER TABLE project_member_role ADD CONSTRAINT fk_632e51cd64ab9629 FOREIGN KEY (project_member_id) REFERENCES project_member (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
