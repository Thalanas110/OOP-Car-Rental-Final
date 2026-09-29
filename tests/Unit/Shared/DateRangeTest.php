<?php

declare(strict_types=1);

namespace Tests\Unit\Shared;

use App\Shared\Domain\ValueObject\DateRange;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class DateRangeTest extends TestCase
{
    public function testCalculatesAtLeastOneBillableDay(): void
    {
        $range = DateRange::between(
            new DateTimeImmutable('2026-01-01 10:00:00'),
            new DateTimeImmutable('2026-01-01 12:00:00'),
        );

        self::assertSame(1, $range->billableDays());
    }

    public function testDetectsOverlappingRangesButAllowsAdjacentRanges(): void
    {
        $range = DateRange::between(new DateTimeImmutable('2026-01-01'), new DateTimeImmutable('2026-01-03'));
        $overlap = DateRange::between(new DateTimeImmutable('2026-01-02'), new DateTimeImmutable('2026-01-04'));
        $adjacent = DateRange::between(new DateTimeImmutable('2026-01-03'), new DateTimeImmutable('2026-01-05'));

        self::assertTrue($range->overlaps($overlap));
        self::assertFalse($range->overlaps($adjacent));
    }

    public function testRejectsReversedDates(): void
    {
        $this->expectException(InvalidArgumentException::class);

        DateRange::between(new DateTimeImmutable('2026-01-03'), new DateTimeImmutable('2026-01-01'));
    }
}
