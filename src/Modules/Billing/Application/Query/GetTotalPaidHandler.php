<?php

declare(strict_types=1);

namespace App\Modules\Billing\Application\Query;

use App\Modules\Billing\Domain\Repository\PaymentRepository;
use App\Shared\Domain\ValueObject\Money;
use App\Shared\Domain\ValueObject\UserId;

final readonly class GetTotalPaidHandler
{
    public function __construct(private PaymentRepository $payments) {}
    public function __invoke(GetTotalPaid $query): TotalPaidView { return new TotalPaidView($this->payments->totalPaidForUser(UserId::fromInt($query->userId))->toDecimal()); }
}
