<?php

declare(strict_types=1);

namespace App\Tests\Unit\Account\Domain;

use App\Account\Domain\User\Email;
use App\Account\Domain\User\InvalidEmailException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EmailTest extends TestCase
{
    #[Test]
    public function itHoldsAnEmailValue(): void
    {
        $email = Email::fromString('hugues@example.com');

        self::assertSame('hugues@example.com', $email->toString());
    }

    #[Test]
    #[DataProvider('valuesNeedingNormalization')]
    public function itNormalizesByTrimmingAndLowercasing(
        string $rawInput,
        string $expectedNormalized,
    ): void {
        self::assertSame(
            $expectedNormalized,
            Email::fromString($rawInput)->toString(),
        );
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function valuesNeedingNormalization(): iterable
    {
        yield 'trim only' => ['  hugues@example.com  ', 'hugues@example.com'];
        yield 'lowercase only' => ['HUGUES@EXAMPLE.COM', 'hugues@example.com'];
        yield 'mixed case and spaces' => ['  Hugues@Gmail.COM  ', 'hugues@gmail.com'];
        yield 'already normalized stays identical' => ['hugues@example.com', 'hugues@example.com'];
    }

    #[Test]
    #[DataProvider('syntacticallyInvalidValues')]
    public function itRejectsASyntacticallyInvalidValue(string $invalidValue): void
    {
        $this->expectException(InvalidEmailException::class);

        Email::fromString($invalidValue);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function syntacticallyInvalidValues(): iterable
    {
        yield 'empty string' => [''];
        yield 'whitespace only' => ['   '];
        yield 'no at sign' => ['not-an-email'];
        yield 'double at sign' => ['a@b@c.com'];
        yield 'empty local part' => ['@example.com'];
        yield 'empty domain part' => ['hugues@'];
        yield 'spaces in the middle' => ['hugues @example.com'];
    }
}
