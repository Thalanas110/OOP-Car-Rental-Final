<?php

declare(strict_types=1);

namespace App\Modules\Billing\Domain\Entity;

use App\Shared\Domain\ValueObject\BookingId;
use App\Shared\Domain\ValueObject\Money;
use InvalidArgumentException;

final readonly class Payment
{
    private function __construct(private BookingId $bookingId, private Money $amount) {}

    public static function record(BookingId $bookingId, Money $amount): self
    {
        if ($amount->toMinorUnits() < 1) {
            throw new InvalidArgumentException('Payment amount must be positive.');
        }
        return new self($bookingId, $amount);
    }

    public function bookingId(): BookingId
    {
        return $this->bookingId;
    }
    public function amount(): Money
    {
        return $this->amount;
    }
}
