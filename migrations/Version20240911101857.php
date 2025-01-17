<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240911101857 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE issue_type (id INT NOT NULL, label VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE issue ADD type_id INT NOT NULL');
        $this->addSql('ALTER TABLE issue ADD CONSTRAINT FK_12AD233EC54C8C93 FOREIGN KEY (type_id) REFERENCES issue_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_12AD233EC54C8C93 ON issue (type_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE issue_type');
        $this->addSql('ALTER TABLE issue DROP CONSTRAINT FK_12AD233EC54C8C93');
        $this->addSql('DROP INDEX IDX_12AD233EC54C8C93');
        $this->addSql('ALTER TABLE issue DROP type_id');
    }
}
