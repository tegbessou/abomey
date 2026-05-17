<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260515152753 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create games table for the Tarot bounded context';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE games (id VARCHAR(36) NOT NULL, owner_id VARCHAR(36) NOT NULL, name VARCHAR(255) NOT NULL, mode SMALLINT NOT NULL, participant_ids JSON NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE games');
    }
}
