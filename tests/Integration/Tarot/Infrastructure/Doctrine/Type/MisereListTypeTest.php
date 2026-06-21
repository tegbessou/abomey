<?php

declare(strict_types=1);

namespace App\Tests\Integration\Tarot\Infrastructure\Doctrine\Type;

use App\Tarot\Domain\Game\Misere;
use App\Tarot\Domain\Game\MisereType;
use App\Tarot\Infrastructure\Doctrine\Type\MisereListType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class MisereListTypeTest extends KernelTestCase
{
    private MisereListType $type;
    private AbstractPlatform $platform;

    protected function setUp(): void
    {
        self::bootKernel();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->type = new MisereListType();
        $this->platform = $entityManager->getConnection()->getDatabasePlatform();
    }

    #[Test]
    public function itRoundTripsAnEmptyList(): void
    {
        $database = $this->type->convertToDatabaseValue([], $this->platform);

        self::assertSame([], $this->type->convertToPHPValue($database, $this->platform));
    }

    #[Test]
    public function itRoundTripsMiseresPreservingOrderAndValues(): void
    {
        $miseres = [
            new Misere(announcerId: 'p-1', type: MisereType::Atouts),
            new Misere(announcerId: 'p-1', type: MisereType::Tete),
        ];

        $database = $this->type->convertToDatabaseValue($miseres, $this->platform);
        $reloaded = $this->type->convertToPHPValue($database, $this->platform);

        self::assertEquals($miseres, $reloaded);
    }
}
