<?php

declare(strict_types=1);

namespace App\Tests\Panther;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Panther\PantherTestCase;

abstract class AbomeyPantherTestCase extends PantherTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->truncateTables();
    }

    protected function tearDown(): void
    {
        $this->truncateTables();
        parent::tearDown();
    }

    private function truncateTables(): void
    {
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $conn = $em->getConnection();
        $conn->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
        foreach (['games', 'players', 'users'] as $table) {
            $conn->executeStatement('TRUNCATE TABLE '.$table);
        }
        $conn->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
