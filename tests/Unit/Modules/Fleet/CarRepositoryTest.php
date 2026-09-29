<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Fleet;

use App\Modules\Fleet\Domain\Entity\Car;
use App\Modules\Fleet\Domain\Repository\CarRepository;
use App\Modules\Fleet\Infrastructure\Persistence\PdoCarRepository;
use App\Shared\Domain\ValueObject\Money;
use PDO;
use PHPUnit\Framework\TestCase;

final class CarRepositoryTest extends TestCase
{
    private CarRepository $repository;

    protected function setUp(): void
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('CREATE TABLE carstable (carID INTEGER PRIMARY KEY AUTOINCREMENT, car_brand TEXT NOT NULL, car_model TEXT NOT NULL, manu_year TEXT NULL, daily_rate NUMERIC NOT NULL, AC INTEGER NULL, seating_capacity INTEGER NOT NULL, plate_no TEXT NULL, isdeleted INTEGER NOT NULL DEFAULT 0)');
        $this->repository = new PdoCarRepository($pdo, Money::fromDecimal('200000.00'));
    }

    public function testSavesFindsAndListsActiveCars(): void
    {
        $car = Car::register('Toyota', 'Vios', '2020', Money::fromDecimal('1800.00'), true, 5, 'ABC1234');
        $saved = $this->repository->save($car);

        self::assertSame('Toyota', $this->repository->find($saved->id())->brand());
        self::assertCount(1, $this->repository->listActive(false));
    }

    public function testHidesLuxuryCarsUnlessRequested(): void
    {
        $this->repository->save(Car::register('Rolls-Royce', 'Phantom', null, Money::fromDecimal('250000.00'), false, 4, null));

        self::assertCount(0, $this->repository->listActive(false));
        self::assertCount(1, $this->repository->listActive(true));
    }
}
