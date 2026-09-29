<?php

declare(strict_types=1);

namespace App\Modules\Fleet\Application\View;

use App\Modules\Fleet\Domain\Entity\Car;

final readonly class CarView
{
    public function __construct(public int $carId, public string $brand, public string $model, public string $dailyRate, public int $seatingCapacity, public ?string $plateNumber, public bool $archived) {}

    public static function fromEntity(Car $car): self
    {
        return new self($car->id()?->toInt() ?? 0, $car->brand(), $car->model(), $car->dailyRate()->toDecimal(), $car->seatingCapacity(), $car->plateNumber(), $car->isArchived());
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return ['carID' => $this->carId, 'car_brand' => $this->brand, 'car_model' => $this->model, 'daily_rate' => $this->dailyRate, 'seating_capacity' => $this->seatingCapacity, 'plate_no' => $this->plateNumber, 'isdeleted' => $this->archived];
    }
}
