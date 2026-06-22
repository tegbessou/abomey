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

    #[\Override]
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?PlayerId
    {
        if (null === $value) {
            return null;
        }

        if (!is_string($value)) {
            throw new \LogicException('Expected string.');
        }

        return PlayerId::fromString($value);
    }

    #[\Override]
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof PlayerId) {
            throw new \LogicException('Expected PlayerId.');
        }

        return $value->toString();
    }
}
