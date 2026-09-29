<?php

declare(strict_types=1);

namespace App\Modules\Billing\Domain\Service;

use App\Shared\Domain\ValueObject\Money;

final readonly class VipPolicy
{
    public function __construct(private Money $threshold) {}

    public function pointsAfter(Money $current, Money $payment): Money
    {
        return $current->plus($payment);
    }

    public function hasAccess(Money $points): bool
    {
        return $points->isGreaterThanOrEqualTo($this->threshold);
    }
}
