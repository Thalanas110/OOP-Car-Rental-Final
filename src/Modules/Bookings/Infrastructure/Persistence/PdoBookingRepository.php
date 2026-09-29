<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Infrastructure\Persistence;

use App\Modules\Bookings\Domain\Entity\Booking;
use App\Modules\Bookings\Domain\Repository\BookingRepository;
use App\Shared\Domain\ValueObject\BookingId;
use App\Shared\Domain\ValueObject\CarId;
use App\Shared\Domain\ValueObject\DateRange;
use App\Shared\Domain\ValueObject\Money;
use App\Shared\Domain\ValueObject\UserId;
use DateTimeImmutable;
use PDO;

final readonly class PdoBookingRepository implements BookingRepository
{
    public function __construct(private PDO $pdo) {}

    public function hasOverlap(CarId $carId, DateRange $dateRange): bool
    {
        $statement = $this->pdo->prepare('SELECT 1 FROM bookingtable WHERE carID = :car_id AND book_date < :return_date AND return_date > :book_date LIMIT 1');
        $statement->execute(['car_id' => $carId->toInt(), 'book_date' => $dateRange->start->format('Y-m-d H:i:s'), 'return_date' => $dateRange->end->format('Y-m-d H:i:s')]);
        return $statement->fetchColumn() !== false;
    }

    public function save(Booking $booking): Booking
    {
        $statement = $this->pdo->prepare('INSERT INTO bookingtable (carID, userID, daily_rate, book_date, return_date, total_cost) VALUES (:car_id, :user_id, :daily_rate, :book_date, :return_date, :total_cost)');
        $statement->execute([
            'car_id' => $booking->carId()->toInt(), 'user_id' => $booking->userId()->toInt(), 'daily_rate' => $booking->dailyRate()->toDecimal(),
            'book_date' => $booking->dateRange()->start->format('Y-m-d H:i:s'), 'return_date' => $booking->dateRange()->end->format('Y-m-d H:i:s'), 'total_cost' => $booking->totalCost()->toDecimal(),
        ]);

        return Booking::reconstitute(BookingId::fromInt((int) $this->pdo->lastInsertId()), $booking->carId(), $booking->userId(), $booking->dateRange(), $booking->dailyRate(), $booking->totalCost());
    }

    public function find(BookingId $id): ?Booking
    {
        $statement = $this->pdo->prepare('SELECT bookingID, carID, userID, daily_rate, book_date, return_date, total_cost FROM bookingtable WHERE bookingID = :id LIMIT 1');
        $statement->execute(['id' => $id->toInt()]);
        $row = $statement->fetch();
        if (!is_array($row)) { return null; }
        return Booking::reconstitute(BookingId::fromInt($this->intValue($row, 'bookingID')), CarId::fromInt($this->intValue($row, 'carID')), UserId::fromInt($this->intValue($row, 'userID')), DateRange::between(new DateTimeImmutable($this->stringValue($row, 'book_date')), new DateTimeImmutable($this->stringValue($row, 'return_date'))), Money::fromDecimal($this->stringValue($row, 'daily_rate')), Money::fromDecimal($this->stringValue($row, 'total_cost')));
    }

    /** @param array<mixed, mixed> $row */
    private function intValue(array $row, string $key): int
    {
        $value = $row[$key] ?? 0;
        return is_int($value) ? $value : (is_numeric($value) ? (int) $value : 0);
    }

    /** @param array<mixed, mixed> $row */
    private function stringValue(array $row, string $key): string
    {
        $value = $row[$key] ?? '';
        return is_scalar($value) ? (string) $value : '';
    }
}
