<?php

declare(strict_types=1);

namespace App\Modules\Fleet\Infrastructure\Persistence;

use App\Modules\Fleet\Domain\Entity\Car;
use App\Modules\Fleet\Domain\Repository\CarRepository;
use App\Shared\Domain\ValueObject\CarId;
use App\Shared\Domain\ValueObject\Money;
use PDO;

final readonly class PdoCarRepository implements CarRepository
{
    public function __construct(private PDO $pdo, private Money $luxuryThreshold) {}

    public function find(CarId $id): ?Car
    {
        $statement = $this->pdo->prepare('SELECT carID, car_brand, car_model, manu_year, daily_rate, AC, seating_capacity, plate_no, isdeleted FROM carstable WHERE carID = :id LIMIT 1');
        $statement->execute(['id' => $id->toInt()]);

        return $this->hydrate($statement->fetch());
    }

    public function listActive(bool $includeLuxury): array
    {
        $sql = 'SELECT carID, car_brand, car_model, manu_year, daily_rate, AC, seating_capacity, plate_no, isdeleted FROM carstable WHERE isdeleted = 0';
        $parameters = [];
        if (!$includeLuxury) {
            $sql .= ' AND daily_rate < :threshold';
            $parameters['threshold'] = $this->luxuryThreshold->toDecimal();
        }
        $sql .= ' ORDER BY carID';
        $statement = $this->pdo->prepare($sql);
        $statement->execute($parameters);

        return array_values(array_filter(array_map(fn(array $row): ?Car => $this->hydrate($row), $statement->fetchAll())));
    }

    public function save(Car $car): Car
    {
        $values = [
            'brand' => $car->brand(), 'model' => $car->model(), 'manu_year' => $car->manufactureYear(),
            'daily_rate' => $car->dailyRate()->toDecimal(), 'ac' => $car->airConditioned() ? 1 : 0,
            'seating_capacity' => $car->seatingCapacity(), 'plate_no' => $car->plateNumber(), 'isdeleted' => $car->isArchived() ? 1 : 0,
        ];
        if ($car->id() === null) {
            $statement = $this->pdo->prepare('INSERT INTO carstable (car_brand, car_model, manu_year, daily_rate, AC, seating_capacity, plate_no, isdeleted) VALUES (:brand, :model, :manu_year, :daily_rate, :ac, :seating_capacity, :plate_no, :isdeleted)');
            $statement->execute($values);
            $car->assignId(CarId::fromInt((int) $this->pdo->lastInsertId()));

            return $car;
        }

        $values['id'] = $car->id()->toInt();
        $statement = $this->pdo->prepare('UPDATE carstable SET car_brand = :brand, car_model = :model, manu_year = :manu_year, daily_rate = :daily_rate, AC = :ac, seating_capacity = :seating_capacity, plate_no = :plate_no, isdeleted = :isdeleted WHERE carID = :id');
        $statement->execute($values);

        return $car;
    }

    public function archive(CarId $id): void
    {
        $statement = $this->pdo->prepare('UPDATE carstable SET isdeleted = 1 WHERE carID = :id');
        $statement->execute(['id' => $id->toInt()]);
    }

    public function delete(CarId $id): void
    {
        $statement = $this->pdo->prepare('DELETE FROM carstable WHERE carID = :id');
        $statement->execute(['id' => $id->toInt()]);
    }

    private function hydrate(mixed $row): ?Car
    {
        if (!is_array($row)) {
            return null;
        }

        return Car::reconstitute(
            CarId::fromInt($this->intValue($row, 'carID')),
            $this->stringValue($row, 'car_brand'),
            $this->stringValue($row, 'car_model'),
            $this->nullableStringValue($row, 'manu_year'),
            Money::fromDecimal($this->stringValue($row, 'daily_rate')),
            $this->boolValue($row, 'AC'),
            $this->intValue($row, 'seating_capacity'),
            $this->nullableStringValue($row, 'plate_no'),
            $this->boolValue($row, 'isdeleted'),
        );
    }

    /** @param array<mixed, mixed> $row */
    private function intValue(array $row, string $key): int
    {
        $value = $row[$key] ?? 0;
        return is_int($value) ? $value : (is_numeric($value) ? (int) $value : 0);
    }

    /** @param array<mixed, mixed> $row */
    private function stringValue(array $row, string $key): string
    {
        $value = $row[$key] ?? '';
        return is_scalar($value) ? (string) $value : '';
    }

    /** @param array<mixed, mixed> $row */
    private function nullableStringValue(array $row, string $key): ?string
    {
        $value = $row[$key] ?? null;
        return $value === null ? null : (is_scalar($value) ? (string) $value : null);
    }

    /** @param array<mixed, mixed> $row */
    private function boolValue(array $row, string $key): bool
    {
        $value = $row[$key] ?? false;
        return is_bool($value) ? $value : (is_numeric($value) ? (int) $value !== 0 : $value === 'true');
    }
}
