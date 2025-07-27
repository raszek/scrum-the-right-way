<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250727151801 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "user" DROP activation_code');
        $this->addSql('ALTER TABLE "user" DROP reset_password_code');
        $this->addSql('ALTER TABLE "user" DROP activation_code_send_date');
        $this->addSql('ALTER TABLE "user" DROP reset_password_code_send_date');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "user" ADD activation_code VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD reset_password_code VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD activation_code_send_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD reset_password_code_send_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
    }
}
