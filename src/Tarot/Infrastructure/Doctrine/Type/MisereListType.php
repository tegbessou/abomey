<?php

declare(strict_types=1);

namespace App\Tarot\Infrastructure\Doctrine\Type;

use App\Tarot\Domain\Game\Misere;
use App\Tarot\Domain\Game\MisereType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\JsonType;

final class MisereListType extends JsonType
{
    /** @return list<Misere> */
    #[\Override]
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): array
    {
        $rows = parent::convertToPHPValue($value, $platform);
        if (null === $rows) {
            return [];
        }
        if (!is_array($rows)) {
            throw new \LogicException('Expected a JSON array of misères.');
        }

        $miseres = [];
        foreach ($rows as $row) {
            $miseres[] = $this->misereFromRow($row);
        }

        return $miseres;
    }

    #[\Override]
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): string
    {
        if (!is_array($value)) {
            throw new \LogicException('Expected a list of Misere.');
        }

        $rows = [];
        foreach ($value as $misere) {
            if (!$misere instanceof Misere) {
                throw new \LogicException('Expected a Misere.');
            }
            $rows[] = [
                'announcerId' => $misere->announcerId,
                'type' => $misere->type->value,
            ];
        }

        return parent::convertToDatabaseValue($rows, $platform);
    }

    private function misereFromRow(mixed $row): Misere
    {
        if (!is_array($row)) {
            throw new \LogicException('Malformed misère row.');
        }

        $announcerId = $row['announcerId'] ?? null;
        $type = $row['type'] ?? null;
        if (!is_string($announcerId) || !is_string($type)) {
            throw new \LogicException('Malformed misère row.');
        }

        return new Misere($announcerId, MisereType::from($type));
    }
}
