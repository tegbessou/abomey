<?php

declare(strict_types=1);

namespace App\Tests\Integration\Tarot\Infrastructure\Doctrine\Type;

use App\Tarot\Domain\Game\Poignee;
use App\Tarot\Domain\Game\PoigneeSize;
use App\Tarot\Infrastructure\Doctrine\Type\PoigneeListType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class PoigneeListTypeTest extends KernelTestCase
{
    private PoigneeListType $type;
    private AbstractPlatform $platform;

    protected function setUp(): void
    {
        self::bootKernel();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->type = new PoigneeListType();
        $this->platform = $entityManager->getConnection()->getDatabasePlatform();
    }

    #[Test]
    public function itRoundTripsAnEmptyList(): void
    {
        $database = $this->type->convertToDatabaseValue([], $this->platform);

        self::assertSame([], $this->type->convertToPHPValue($database, $this->platform));
    }

    #[Test]
    public function itRoundTripsPoigneesPreservingOrderAndValues(): void
    {
        $poignees = [
            new Poignee(announcerId: 'p-1', size: PoigneeSize::Single),
            new Poignee(announcerId: 'p-3', size: PoigneeSize::Double),
        ];

        $database = $this->type->convertToDatabaseValue($poignees, $this->platform);
        $reloaded = $this->type->convertToPHPValue($database, $this->platform);

        self::assertEquals($poignees, $reloaded);
    }
}
