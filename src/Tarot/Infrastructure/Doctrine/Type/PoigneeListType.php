<?php

declare(strict_types=1);

namespace App\Tarot\Infrastructure\Doctrine\Type;

use App\Tarot\Domain\Game\Poignee;
use App\Tarot\Domain\Game\PoigneeSize;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\JsonType;

final class PoigneeListType extends JsonType
{
    /** @return list<Poignee> */
    #[\Override]
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): array
    {
        $rows = parent::convertToPHPValue($value, $platform);
        if (null === $rows) {
            return [];
        }
        if (!is_array($rows)) {
            throw new \LogicException('Expected a JSON array of poignées.');
        }

        $poignees = [];
        foreach ($rows as $row) {
            $poignees[] = $this->poigneeFromRow($row);
        }

        return $poignees;
    }

    #[\Override]
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): string
    {
        if (!is_array($value)) {
            throw new \LogicException('Expected a list of Poignee.');
        }

        $rows = [];
        foreach ($value as $poignee) {
            if (!$poignee instanceof Poignee) {
                throw new \LogicException('Expected a Poignee.');
            }
            $rows[] = [
                'announcerId' => $poignee->announcerId,
                'size' => $poignee->size->value,
            ];
        }

        return parent::convertToDatabaseValue($rows, $platform);
    }

    private function poigneeFromRow(mixed $row): Poignee
    {
        if (!is_array($row)) {
            throw new \LogicException('Malformed poignée row.');
        }

        $announcerId = $row['announcerId'] ?? null;
        $size = $row['size'] ?? null;
        if (!is_string($announcerId) || !is_string($size)) {
            throw new \LogicException('Malformed poignée row.');
        }

        return new Poignee($announcerId, PoigneeSize::from($size));
    }
}
