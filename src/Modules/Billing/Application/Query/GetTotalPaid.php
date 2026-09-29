<?php

declare(strict_types=1);

namespace App\Modules\Billing\Application\Query;

final readonly class GetTotalPaid
{
    public function __construct(public int $userId) {}
}
