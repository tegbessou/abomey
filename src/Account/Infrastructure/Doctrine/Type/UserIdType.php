<?php

declare(strict_types=1);

namespace App\Account\Infrastructure\Doctrine\Type;

use App\Account\Domain\User\UserId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class UserIdType extends Type
{
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL($column);
    }

    #[\Override]
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?UserId
    {
        if (null === $value) {
            return null;
        }

        if (!is_string($value)) {
            throw new \LogicException('Expected string.');
        }

        return UserId::fromString($value);
    }

    #[\Override]
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof UserId) {
            throw new \LogicException('Expected UserId.');
        }

        return $value->toString();
    }

    public function getName(): string
    {
        return 'user_id';
    }
}
