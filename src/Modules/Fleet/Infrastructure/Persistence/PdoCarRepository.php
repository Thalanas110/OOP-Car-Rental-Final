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
    public function __construct(private PDO $pdo, private Money $luxuryThreshold)
    {
    }

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

        return array_values(array_filter(array_map(fn (array $row): ?Car => $this->hydrate($row), $statement->fetchAll())));
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

    /** @param array<string, mixed>|false $row */
    private function hydrate(array|false $row): ?Car
    {
        if ($row === false) {
            return null;
        }

        return Car::reconstitute(
            CarId::fromInt((int) $row['carID']), (string) $row['car_brand'], (string) $row['car_model'], $row['manu_year'] !== null ? (string) $row['manu_year'] : null,
            Money::fromDecimal((string) $row['daily_rate']), (bool) $row['AC'], (int) $row['seating_capacity'], $row['plate_no'] !== null ? (string) $row['plate_no'] : null, (bool) $row['isdeleted'],
        );
    }
}
