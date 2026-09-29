<?php

declare(strict_types=1);

namespace App\Modules\Fleet\Application\Command;

final readonly class CreateCar
{
    public function __construct(public string $brand, public string $model, public ?string $manufactureYear, public string $dailyRate, public bool $airConditioned, public int $seatingCapacity, public ?string $plateNumber) {}
}
