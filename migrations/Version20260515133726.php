<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260515133726 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add privacy consent columns to users';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users ADD privacy_consent_version VARCHAR(50) DEFAULT NULL, ADD privacy_consent_accepted_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users DROP privacy_consent_version, DROP privacy_consent_accepted_at');
    }
}
