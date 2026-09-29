<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Application\Command;

final readonly class CreateBooking
{
    public function __construct(public int $carId, public int $userId, public string $bookDate, public string $returnDate) {}
}
