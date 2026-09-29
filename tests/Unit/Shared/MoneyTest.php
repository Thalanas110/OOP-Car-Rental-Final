<?php

declare(strict_types=1);

namespace Tests\Unit\Shared;

use App\Shared\Domain\ValueObject\Money;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    public function testPreservesTwoDecimalPrecision(): void
    {
        $total = Money::fromDecimal('10.25')->plus(Money::fromDecimal('2.75'));

        self::assertSame('13.00', $total->toDecimal());
    }

    public function testRejectsNegativeAmounts(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::fromDecimal('-0.01');
    }
}
