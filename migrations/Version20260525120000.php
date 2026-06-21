<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260525120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create deals table for classic deal recording within a Game';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE deals (
                id INT AUTO_INCREMENT NOT NULL,
                game_id VARCHAR(36) NOT NULL,
                position SMALLINT NOT NULL,
                active_player_ids JSON NOT NULL,
                taker_id VARCHAR(36) NOT NULL,
                contract VARCHAR(20) NOT NULL,
                bouts SMALLINT NOT NULL,
                points_scored SMALLINT NOT NULL,
                PRIMARY KEY (id),
                INDEX IDX_DEAL_GAME (game_id),
                CONSTRAINT FK_DEAL_GAME FOREIGN KEY (game_id) REFERENCES games (id) ON DELETE CASCADE
            ) DEFAULT CHARACTER SET utf8mb4
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE deals');
    }
}
