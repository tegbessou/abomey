<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260620190037 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add nullable partner_id to deals (Tarot à 5 — désignation du Partenaire).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE deals ADD partner_id VARCHAR(36) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE deals DROP partner_id');
    }
}
