<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260524120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add created_at column to games table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE games ADD created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE games DROP created_at');
    }
}
