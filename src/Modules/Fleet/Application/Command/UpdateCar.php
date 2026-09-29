<?php

declare(strict_types=1);

namespace App\Modules\Fleet\Application\Command;

final readonly class UpdateCar
{
    public function __construct(public int $carId, public string $dailyRate, public ?string $plateNumber) {}
}
