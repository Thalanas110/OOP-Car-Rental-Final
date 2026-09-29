<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Domain\Repository;

use App\Modules\Bookings\Domain\Entity\Booking;
use App\Shared\Domain\ValueObject\BookingId;
use App\Shared\Domain\ValueObject\CarId;
use App\Shared\Domain\ValueObject\DateRange;

interface BookingRepository
{
    public function hasOverlap(CarId $carId, DateRange $dateRange): bool;
    public function save(Booking $booking): Booking;
    public function find(BookingId $id): ?Booking;
}
