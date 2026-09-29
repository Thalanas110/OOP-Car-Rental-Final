<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Bookings;

use App\Modules\Bookings\Application\Command\CreateBooking;
use App\Modules\Bookings\Application\Command\CreateBookingHandler;
use App\Modules\Bookings\Domain\Repository\BookingRepository;
use App\Modules\Bookings\Domain\Service\BookingPolicy;
use App\Modules\Fleet\Application\Port\CarRateReader;
use App\Modules\Identity\Application\Port\VipStatusReader;
use App\Modules\Users\Application\Port\UserExistenceReader;
use App\Shared\Application\Transaction\TransactionManager;
use App\Shared\Domain\ValueObject\BookingId;
use App\Shared\Domain\ValueObject\CarId;
use App\Shared\Domain\ValueObject\Money;
use App\Shared\Domain\ValueObject\UserId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class CreateBookingHandlerTest extends TestCase
{
    public function testCreatesBookingThroughPortsAndTransaction(): void
    {
        $bookings = new class implements BookingRepository {
            public function hasOverlap(CarId $carId, \App\Shared\Domain\ValueObject\DateRange $dateRange): bool
            {
                return false;
            }
            public function save(\App\Modules\Bookings\Domain\Entity\Booking $booking): \App\Modules\Bookings\Domain\Entity\Booking
            {
                return \App\Modules\Bookings\Domain\Entity\Booking::reconstitute(BookingId::fromInt(22), $booking->carId(), $booking->userId(), $booking->dateRange(), $booking->dailyRate(), $booking->totalCost());
            }
            public function find(BookingId $id): ?\App\Modules\Bookings\Domain\Entity\Booking
            {
                return null;
            }
        };
        $users = new class implements UserExistenceReader {
            public function exists(UserId $userId): bool
            {
                return true;
            }
        };
        $cars = new class implements CarRateReader {
            public function rateFor(CarId $carId): ?Money
            {
                return Money::fromDecimal('1500.00');
            }
        };
        $vip = new class implements VipStatusReader {
            public function isVip(UserId $userId): bool
            {
                return false;
            }
        };
        $transactions = new class implements TransactionManager {
            public function run(callable $operation): mixed
            {
                return $operation();
            }
        };

        $view = (new CreateBookingHandler($bookings, $users, $cars, $vip, new BookingPolicy(), $transactions, Money::fromDecimal('200000.00')))(new CreateBooking(2, 7, '2026-10-01', '2026-10-04'));

        self::assertSame(22, $view->bookingId);
        self::assertSame('4500.00', $view->totalCost);
    }
}
