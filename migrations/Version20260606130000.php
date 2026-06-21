<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260606130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add chelem column to deals table for T2b';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE deals ADD chelem VARCHAR(20) NOT NULL DEFAULT 'none'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE deals DROP chelem');
    }
}
