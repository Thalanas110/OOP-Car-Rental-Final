<?php

declare(strict_types=1);

namespace App\Modules\Billing\Application\Query;

final readonly class TotalPaidView
{
    public function __construct(public string $totalPaid) {}
    /** @return array{total_paid: string} */
    public function toArray(): array
    {
        return ['total_paid' => $this->totalPaid];
    }
}
