<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260515130415 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add owner_id column to players for per-user isolation';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE players ADD owner_id VARCHAR(36) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE players DROP owner_id');
    }
}
