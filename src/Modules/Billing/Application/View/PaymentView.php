<?php

declare(strict_types=1);

namespace App\Modules\Billing\Application\View;

use App\Modules\Billing\Domain\Entity\Payment;

final readonly class PaymentView
{
    public function __construct(public int $bookingId, public string $amount) {}
    public static function fromEntity(Payment $payment): self
    {
        return new self($payment->bookingId()->toInt(), $payment->amount()->toDecimal());
    }
    /** @return array{bookingID: int, amount_paid: string} */
    public function toArray(): array
    {
        return ['bookingID' => $this->bookingId, 'amount_paid' => $this->amount];
    }
}
