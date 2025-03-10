<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250301144744 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE sprint (id INT GENERATED BY DEFAULT AS IDENTITY NOT NULL, number INT NOT NULL, started_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, ended_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, project_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_EF8055B7166D1F9C ON sprint (project_id)');
        $this->addSql('CREATE TABLE sprint_goal (id INT GENERATED BY DEFAULT AS IDENTITY NOT NULL, name VARCHAR(1024) NOT NULL, sprint_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C71E44028C24077B ON sprint_goal (sprint_id)');
        $this->addSql('CREATE TABLE sprint_goal_issue (id INT GENERATED BY DEFAULT AS IDENTITY NOT NULL, sprint_goal_id INT NOT NULL, issue_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5C083049BDFA6FB ON sprint_goal_issue (sprint_goal_id)');
        $this->addSql('CREATE INDEX IDX_5C0830495E7AA58C ON sprint_goal_issue (issue_id)');
        $this->addSql('ALTER TABLE sprint ADD CONSTRAINT FK_EF8055B7166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sprint_goal ADD CONSTRAINT FK_C71E44028C24077B FOREIGN KEY (sprint_id) REFERENCES sprint (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sprint_goal_issue ADD CONSTRAINT FK_5C083049BDFA6FB FOREIGN KEY (sprint_goal_id) REFERENCES sprint_goal (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sprint_goal_issue ADD CONSTRAINT FK_5C0830495E7AA58C FOREIGN KEY (issue_id) REFERENCES issue (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE sprint DROP CONSTRAINT FK_EF8055B7166D1F9C');
        $this->addSql('ALTER TABLE sprint_goal DROP CONSTRAINT FK_C71E44028C24077B');
        $this->addSql('ALTER TABLE sprint_goal_issue DROP CONSTRAINT FK_5C083049BDFA6FB');
        $this->addSql('ALTER TABLE sprint_goal_issue DROP CONSTRAINT FK_5C0830495E7AA58C');
        $this->addSql('DROP TABLE sprint');
        $this->addSql('DROP TABLE sprint_goal');
        $this->addSql('DROP TABLE sprint_goal_issue');
    }
}
