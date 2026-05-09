<?php

declare(strict_types=1);

namespace App\Tarot\Infrastructure\Doctrine\Type;

use App\Tarot\Domain\Player\PlayerId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class PlayerIdType extends Type
{
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL($column);
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?PlayerId
    {
        if (null === $value) {
            return null;
        }

        return PlayerId::fromString((string) $value);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        return $value->toString();
    }

    public function getName(): string
    {
        return 'player_id';
    }
}
