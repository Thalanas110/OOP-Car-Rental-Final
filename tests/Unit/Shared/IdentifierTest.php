<?php

declare(strict_types=1);

namespace Tests\Unit\Shared;

use App\Shared\Domain\ValueObject\BookingId;
use App\Shared\Domain\ValueObject\CarId;
use App\Shared\Domain\ValueObject\UserId;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class IdentifierTest extends TestCase
{
    public function testRoundTripsTypedIdentifiers(): void
    {
        self::assertSame(7, UserId::fromInt(7)->toInt());
        self::assertSame(12, CarId::fromInt(12)->toInt());
        self::assertSame(21, BookingId::fromInt(21)->toInt());
    }

    public function testRejectsNonPositiveValues(): void
    {
        $this->expectException(InvalidArgumentException::class);

        UserId::fromInt(0);
    }
}
