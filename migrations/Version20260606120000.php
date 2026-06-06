<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260606120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add petit_au_bout column to deals table for T2a';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE deals ADD petit_au_bout VARCHAR(10) NOT NULL DEFAULT 'none'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE deals DROP petit_au_bout');
    }
}
