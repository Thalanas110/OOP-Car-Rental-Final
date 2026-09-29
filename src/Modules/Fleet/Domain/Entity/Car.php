<?php

declare(strict_types=1);

namespace App\Modules\Fleet\Domain\Entity;

use App\Shared\Domain\ValueObject\CarId;
use App\Shared\Domain\ValueObject\Money;
use InvalidArgumentException;

final class Car
{
    private function __construct(
        private ?CarId $id,
        private string $brand,
        private string $model,
        private ?string $manufactureYear,
        private Money $dailyRate,
        private bool $airConditioned,
        private int $seatingCapacity,
        private ?string $plateNumber,
        private bool $archived,
    ) {
        if ($brand === '' || $model === '' || $seatingCapacity < 1) {
            throw new InvalidArgumentException('Car brand, model, and seating capacity are required.');
        }
    }

    public static function register(string $brand, string $model, ?string $manufactureYear, Money $dailyRate, bool $airConditioned, int $seatingCapacity, ?string $plateNumber): self
    {
        return new self(null, trim($brand), trim($model), $manufactureYear !== null ? trim($manufactureYear) : null, $dailyRate, $airConditioned, $seatingCapacity, $plateNumber !== null ? trim($plateNumber) : null, false);
    }

    public static function reconstitute(CarId $id, string $brand, string $model, ?string $manufactureYear, Money $dailyRate, bool $airConditioned, int $seatingCapacity, ?string $plateNumber, bool $archived): self
    {
        return new self($id, $brand, $model, $manufactureYear, $dailyRate, $airConditioned, $seatingCapacity, $plateNumber, $archived);
    }

    public function assignId(CarId $id): void { $this->id = $id; }
    public function id(): ?CarId { return $this->id; }
    public function brand(): string { return $this->brand; }
    public function model(): string { return $this->model; }
    public function manufactureYear(): ?string { return $this->manufactureYear; }
    public function dailyRate(): Money { return $this->dailyRate; }
    public function airConditioned(): bool { return $this->airConditioned; }
    public function seatingCapacity(): int { return $this->seatingCapacity; }
    public function plateNumber(): ?string { return $this->plateNumber; }
    public function isArchived(): bool { return $this->archived; }

    public function updateRateAndPlate(Money $dailyRate, ?string $plateNumber): void
    {
        $this->dailyRate = $dailyRate;
        $this->plateNumber = $plateNumber !== null ? trim($plateNumber) : null;
    }

    public function archive(): void { $this->archived = true; }

    public function isLuxury(Money $threshold): bool
    {
        return $this->dailyRate->isGreaterThanOrEqualTo($threshold);
    }
}
