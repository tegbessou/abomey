<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260621172809 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add the ranking column for Vachette deals and register the vachette discriminator.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE deals ADD ranking JSON DEFAULT NULL, CHANGE type type VARCHAR(20) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE deals DROP ranking, CHANGE type type VARCHAR(20) DEFAULT 'classic' NOT NULL");
    }
}
