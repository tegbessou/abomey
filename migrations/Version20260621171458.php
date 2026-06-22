<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260621171458 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Single Table Inheritance on deals: add the type discriminator and make classic-only columns nullable.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE deals ADD type VARCHAR(20) DEFAULT 'classic' NOT NULL, CHANGE active_player_ids active_player_ids JSON DEFAULT NULL, CHANGE taker_id taker_id VARCHAR(36) DEFAULT NULL, CHANGE contract contract VARCHAR(20) DEFAULT NULL, CHANGE bouts bouts SMALLINT DEFAULT NULL, CHANGE points_scored points_scored SMALLINT DEFAULT NULL, CHANGE petit_au_bout petit_au_bout VARCHAR(10) DEFAULT NULL, CHANGE chelem chelem VARCHAR(20) DEFAULT NULL, CHANGE poignees poignees JSON DEFAULT NULL, CHANGE miseres miseres JSON DEFAULT NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE deals DROP type, CHANGE active_player_ids active_player_ids JSON NOT NULL, CHANGE taker_id taker_id VARCHAR(36) NOT NULL, CHANGE contract contract VARCHAR(20) NOT NULL, CHANGE bouts bouts SMALLINT NOT NULL, CHANGE points_scored points_scored SMALLINT NOT NULL, CHANGE petit_au_bout petit_au_bout VARCHAR(10) NOT NULL, CHANGE chelem chelem VARCHAR(20) NOT NULL, CHANGE poignees poignees JSON NOT NULL, CHANGE miseres miseres JSON NOT NULL');
    }
}
