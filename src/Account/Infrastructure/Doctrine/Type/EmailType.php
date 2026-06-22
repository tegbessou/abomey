<?php

declare(strict_types=1);

namespace App\Account\Infrastructure\Doctrine\Type;

use App\Account\Domain\User\Email;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class EmailType extends Type
{
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL($column);
    }

    #[\Override]
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?Email
    {
        if (null === $value) {
            return null;
        }

        if (!is_string($value)) {
            throw new \LogicException('Expected string.');
        }

        return Email::fromString($value);
    }

    #[\Override]
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof Email) {
            throw new \LogicException('Expected Email.');
        }

        return $value->toString();
    }
}
