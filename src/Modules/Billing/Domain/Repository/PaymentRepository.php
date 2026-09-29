<?php

declare(strict_types=1);

namespace App\Modules\Billing\Domain\Repository;

use App\Modules\Billing\Domain\Entity\Payment;
use App\Shared\Domain\ValueObject\BookingId;
use App\Shared\Domain\ValueObject\Money;
use App\Shared\Domain\ValueObject\UserId;

interface PaymentRepository
{
    public function bookingExists(BookingId $bookingId): bool;
    public function userIdForBooking(BookingId $bookingId): ?UserId;
    public function save(Payment $payment): Payment;
    public function totalPaidForUser(UserId $userId): Money;
}
