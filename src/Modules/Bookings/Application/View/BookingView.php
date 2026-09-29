<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Application\View;

use App\Modules\Bookings\Domain\Entity\Booking;

final readonly class BookingView
{
    public function __construct(public int $bookingId, public int $carId, public int $userId, public string $totalCost, public string $bookDate, public string $returnDate) {}

    public static function fromEntity(Booking $booking): self
    {
        return new self($booking->id()?->toInt() ?? 0, $booking->carId()->toInt(), $booking->userId()->toInt(), $booking->totalCost()->toDecimal(), $booking->dateRange()->start->format(DATE_ATOM), $booking->dateRange()->end->format(DATE_ATOM));
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return ['bookingID' => $this->bookingId, 'carID' => $this->carId, 'userID' => $this->userId, 'total_cost' => $this->totalCost, 'book_date' => $this->bookDate, 'return_date' => $this->returnDate];
    }
}
