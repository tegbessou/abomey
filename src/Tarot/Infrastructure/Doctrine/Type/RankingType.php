<?php

declare(strict_types=1);

namespace App\Tarot\Infrastructure\Doctrine\Type;

use App\Tarot\Domain\Game\Ranking;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\JsonType;

final class RankingType extends JsonType
{
    #[\Override]
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?Ranking
    {
        $players = parent::convertToPHPValue($value, $platform);
        if (null === $players) {
            return null;
        }
        if (!is_array($players)) {
            throw new \LogicException('Expected a JSON array of player ids.');
        }

        $orderedPlayerIds = [];
        foreach ($players as $playerId) {
            if (!is_string($playerId)) {
                throw new \LogicException('Malformed ranking entry.');
            }
            $orderedPlayerIds[] = $playerId;
        }

        return new Ranking($orderedPlayerIds);
    }

    #[\Override]
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }
        if (!$value instanceof Ranking) {
            throw new \LogicException('Expected a Ranking.');
        }

        return parent::convertToDatabaseValue($value->players(), $platform);
    }
}
