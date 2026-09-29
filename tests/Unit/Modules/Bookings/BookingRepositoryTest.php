<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Bookings;

use App\Modules\Bookings\Domain\Entity\Booking;
use App\Modules\Bookings\Domain\Repository\BookingRepository;
use App\Modules\Bookings\Infrastructure\Persistence\PdoBookingRepository;
use App\Shared\Domain\ValueObject\CarId;
use App\Shared\Domain\ValueObject\DateRange;
use App\Shared\Domain\ValueObject\Money;
use App\Shared\Domain\ValueObject\UserId;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;

final class BookingRepositoryTest extends TestCase
{
    private BookingRepository $repository;

    protected function setUp(): void
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('CREATE TABLE bookingtable (bookingID INTEGER PRIMARY KEY AUTOINCREMENT, carID INTEGER NOT NULL, userID INTEGER NOT NULL, daily_rate NUMERIC NOT NULL, book_date TEXT NOT NULL, return_date TEXT NOT NULL, total_cost NUMERIC NOT NULL)');
        $this->repository = new PdoBookingRepository($pdo);
    }

    public function testDetectsOverlapAndPersistsBooking(): void
    {
        $first = Booking::create(CarId::fromInt(2), UserId::fromInt(7), DateRange::between(new DateTimeImmutable('2026-10-01'), new DateTimeImmutable('2026-10-04')), Money::fromDecimal('1500.00'));
        $saved = $this->repository->save($first);

        self::assertNotNull($saved->id());
        self::assertTrue($this->repository->hasOverlap(CarId::fromInt(2), DateRange::between(new DateTimeImmutable('2026-10-03'), new DateTimeImmutable('2026-10-05'))));
        self::assertFalse($this->repository->hasOverlap(CarId::fromInt(2), DateRange::between(new DateTimeImmutable('2026-10-04'), new DateTimeImmutable('2026-10-05'))));
    }
}
