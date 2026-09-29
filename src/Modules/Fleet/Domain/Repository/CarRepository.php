<?php

declare(strict_types=1);

namespace App\Modules\Fleet\Domain\Repository;

use App\Modules\Fleet\Domain\Entity\Car;
use App\Shared\Domain\ValueObject\CarId;

interface CarRepository
{
    public function find(CarId $id): ?Car;

    /** @return list<Car> */
    public function listActive(bool $includeLuxury): array;

    public function save(Car $car): Car;

    public function archive(CarId $id): void;

    public function delete(CarId $id): void;
}
