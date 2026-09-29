<?php

declare(strict_types=1);

namespace App\Modules\Fleet\Application\Port;

use App\Shared\Domain\ValueObject\CarId;
use App\Shared\Domain\ValueObject\Money;

interface CarRateReader
{
    public function rateFor(CarId $carId): ?Money;
}
