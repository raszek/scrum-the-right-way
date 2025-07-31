<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250729204622 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $result = $this->connection->executeQuery('SELECT * FROM "user"');

        while ($user = $result->fetchAssociative()) {
            $this->connection->executeStatement('INSERT INTO profile (avatar_id, avatar_thumb_id) VALUES (NULL, NULL)', [
                'userId' => $user['id']
            ]);

            $profileId = $this->connection->lastInsertId();

            $this->connection->executeStatement('UPDATE "user" SET profile_id = :profileId WHERE id = :userId', [
                'userId' => $user['id'],
                'profileId' => $profileId
            ]);
        }

        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "user" ALTER profile_id SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "user" ALTER profile_id DROP NOT NULL');
    }
}
