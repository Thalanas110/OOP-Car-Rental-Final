<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Domain\Service;

use App\Shared\Domain\Exception\ConflictException;
use App\Shared\Domain\Exception\ForbiddenException;
use App\Shared\Domain\Exception\NotFoundException;
use App\Shared\Domain\ValueObject\DateRange;

final class BookingPolicy
{
    public function assertBookable(bool $userExists, bool $carExists, bool $luxury, bool $vip, bool $overlap, DateRange $dateRange): void
    {
        if (!$userExists) { throw new NotFoundException('User was not found.'); }
        if (!$carExists) { throw new NotFoundException('Car was not found.'); }
        if ($luxury && !$vip) { throw new ForbiddenException('VIP access is required to book a luxury car.'); }
        if ($overlap) { throw new ConflictException('Car is already booked for the selected date range.'); }
    }
}
