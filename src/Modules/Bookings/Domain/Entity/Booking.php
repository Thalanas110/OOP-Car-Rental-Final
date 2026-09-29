<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Domain\Entity;

use App\Shared\Domain\ValueObject\BookingId;
use App\Shared\Domain\ValueObject\CarId;
use App\Shared\Domain\ValueObject\DateRange;
use App\Shared\Domain\ValueObject\Money;
use App\Shared\Domain\ValueObject\UserId;

final readonly class Booking
{
    private function __construct(private ?BookingId $id, private CarId $carId, private UserId $userId, private DateRange $dateRange, private Money $dailyRate, private Money $totalCost) {}

    public static function create(CarId $carId, UserId $userId, DateRange $dateRange, Money $dailyRate): self
    {
        return new self(null, $carId, $userId, $dateRange, $dailyRate, $dailyRate->multipliedBy($dateRange->billableDays()));
    }

    public static function reconstitute(BookingId $id, CarId $carId, UserId $userId, DateRange $dateRange, Money $dailyRate, Money $totalCost): self
    {
        return new self($id, $carId, $userId, $dateRange, $dailyRate, $totalCost);
    }

    public function id(): ?BookingId { return $this->id; }
    public function carId(): CarId { return $this->carId; }
    public function userId(): UserId { return $this->userId; }
    public function dateRange(): DateRange { return $this->dateRange; }
    public function dailyRate(): Money { return $this->dailyRate; }
    public function totalCost(): Money { return $this->totalCost; }
}
