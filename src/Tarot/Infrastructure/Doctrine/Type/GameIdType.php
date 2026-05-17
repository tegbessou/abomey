<?php

declare(strict_types=1);

namespace App\Tarot\Infrastructure\Doctrine\Type;

use App\Tarot\Domain\Game\GameId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class GameIdType extends Type
{
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL($column);
    }

    #[\Override]
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?GameId
    {
        if (null === $value) {
            return null;
        }

        if (!is_string($value)) {
            throw new \LogicException('Expected string.');
        }

        return GameId::fromString($value);
    }

    #[\Override]
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof GameId) {
            throw new \LogicException('Expected GameId.');
        }

        return $value->toString();
    }

    public function getName(): string
    {
        return 'game_id';
    }
}
