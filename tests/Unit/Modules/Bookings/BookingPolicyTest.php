<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Bookings;

use App\Modules\Bookings\Domain\Entity\Booking;
use App\Modules\Bookings\Domain\Service\BookingPolicy;
use App\Shared\Domain\Exception\ConflictException;
use App\Shared\Domain\Exception\ForbiddenException;
use App\Shared\Domain\Exception\NotFoundException;
use App\Shared\Domain\ValueObject\CarId;
use App\Shared\Domain\ValueObject\DateRange;
use App\Shared\Domain\ValueObject\Money;
use App\Shared\Domain\ValueObject\UserId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class BookingPolicyTest extends TestCase
{
    public function testCreatesBookingWithBillableTotal(): void
    {
        $range = DateRange::between(new DateTimeImmutable('2026-10-01'), new DateTimeImmutable('2026-10-04'));
        $booking = Booking::create(CarId::fromInt(2), UserId::fromInt(7), $range, Money::fromDecimal('1500.00'));

        self::assertSame('4500.00', $booking->totalCost()->toDecimal());
    }

    public function testPolicyRejectsOverlapLuxuryAndMissingResources(): void
    {
        $range = DateRange::between(new DateTimeImmutable('2026-10-01'), new DateTimeImmutable('2026-10-04'));
        $policy = new BookingPolicy();

        $this->expectException(ConflictException::class);
        $policy->assertBookable(true, true, true, true, true, $range);
    }

    public function testPolicyRejectsLuxuryWithoutVip(): void
    {
        $range = DateRange::between(new DateTimeImmutable('2026-10-01'), new DateTimeImmutable('2026-10-04'));

        $this->expectException(ForbiddenException::class);
        (new BookingPolicy())->assertBookable(true, true, true, false, false, $range);
    }

    public function testPolicyRejectsMissingUser(): void
    {
        $range = DateRange::between(new DateTimeImmutable('2026-10-01'), new DateTimeImmutable('2026-10-04'));

        $this->expectException(NotFoundException::class);
        (new BookingPolicy())->assertBookable(false, false, true, false, false, $range);
    }
}
