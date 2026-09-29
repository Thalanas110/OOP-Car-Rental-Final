<?php

declare(strict_types=1);

namespace App\Modules\Billing\Application\Command;

final readonly class RecordPayment
{
    public function __construct(public int $bookingId, public string $amount) {}
}
