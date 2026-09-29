<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Fleet;

use App\Modules\Fleet\Domain\Entity\Car;
use App\Shared\Domain\ValueObject\Money;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class CarTest extends TestCase
{
    public function testRegistersUpdatesAndArchivesCar(): void
    {
        $car = Car::register('Toyota', 'Vios', '2020', Money::fromDecimal('1800.00'), true, 5, 'ABC1234');
        $car->updateRateAndPlate(Money::fromDecimal('2000.00'), 'XYZ9999');
        $car->archive();

        self::assertSame('Toyota', $car->brand());
        self::assertSame('2000.00', $car->dailyRate()->toDecimal());
        self::assertSame('XYZ9999', $car->plateNumber());
        self::assertTrue($car->isArchived());
        self::assertTrue($car->isLuxury(Money::fromDecimal('2000.00')));
    }

    public function testRejectsInvalidSeatingCapacity(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Car::register('Toyota', 'Vios', '2020', Money::fromDecimal('1800.00'), true, 0, 'ABC1234');
    }
}
