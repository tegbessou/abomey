<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260606150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add miseres column (JSON) to deals table for T2d';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE deals ADD miseres JSON NOT NULL DEFAULT (JSON_ARRAY())');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE deals DROP miseres');
    }
}
