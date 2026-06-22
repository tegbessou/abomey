<?php

declare(strict_types=1);

namespace App\Tests\Integration\Tarot\Infrastructure\Doctrine\Type;

use App\Tarot\Domain\Game\Ranking;
use App\Tarot\Infrastructure\Doctrine\Type\RankingType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class RankingTypeTest extends KernelTestCase
{
    private RankingType $type;
    private AbstractPlatform $platform;

    protected function setUp(): void
    {
        self::bootKernel();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->type = new RankingType();
        $this->platform = $entityManager->getConnection()->getDatabasePlatform();
    }

    #[Test]
    public function itRoundTripsARankingPreservingOrder(): void
    {
        $ranking = new Ranking(['p-1', 'p-2', 'p-3', 'p-4']);

        $database = $this->type->convertToDatabaseValue($ranking, $this->platform);
        $reloaded = $this->type->convertToPHPValue($database, $this->platform);

        self::assertEquals($ranking, $reloaded);
    }

    #[Test]
    public function itRoundTripsNull(): void
    {
        $database = $this->type->convertToDatabaseValue(null, $this->platform);

        self::assertNull($this->type->convertToPHPValue($database, $this->platform));
    }
}
