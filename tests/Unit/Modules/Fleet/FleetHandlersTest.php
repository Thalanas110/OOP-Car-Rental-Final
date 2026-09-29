<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Fleet;

use App\Modules\Fleet\Application\Command\CreateCar;
use App\Modules\Fleet\Application\Handler\CreateCarHandler;
use App\Modules\Fleet\Application\Handler\GetCarHandler;
use App\Modules\Fleet\Application\Handler\ListCarsHandler;
use App\Modules\Fleet\Infrastructure\Persistence\PdoCarRepository;
use App\Modules\Fleet\Application\Query\GetCar;
use App\Modules\Fleet\Application\Query\ListCars;
use App\Shared\Domain\ValueObject\Money;
use PDO;
use PHPUnit\Framework\TestCase;

final class FleetHandlersTest extends TestCase
{
    public function testCreatesGetsAndListsCars(): void
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('CREATE TABLE carstable (carID INTEGER PRIMARY KEY AUTOINCREMENT, car_brand TEXT NOT NULL, car_model TEXT NOT NULL, manu_year TEXT NULL, daily_rate NUMERIC NOT NULL, AC INTEGER NULL, seating_capacity INTEGER NOT NULL, plate_no TEXT NULL, isdeleted INTEGER NOT NULL DEFAULT 0)');
        $repository = new PdoCarRepository($pdo, Money::fromDecimal('200000.00'));

        $created = (new CreateCarHandler($repository))(new CreateCar('Toyota', 'Vios', '2020', '1800.00', true, 5, 'ABC1234'));

        self::assertSame(1, $created->carId);
        self::assertSame('Toyota', (new GetCarHandler($repository))(new GetCar(1))->brand);
        self::assertCount(1, (new ListCarsHandler($repository))(new ListCars(false)));
    }
}
