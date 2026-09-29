<?php

declare(strict_types=1);

namespace App\Modules\Fleet\Application\Handler;

use App\Modules\Fleet\Application\Command\UpdateCar;
use App\Modules\Fleet\Application\View\CarView;
use App\Modules\Fleet\Domain\Repository\CarRepository;
use App\Shared\Domain\Exception\NotFoundException;
use App\Shared\Domain\ValueObject\CarId;
use App\Shared\Domain\ValueObject\Money;

final readonly class UpdateCarHandler
{
    public function __construct(private CarRepository $cars) {}
    public function __invoke(UpdateCar $command): CarView
    {
        $car = $this->cars->find(CarId::fromInt($command->carId));
        if ($car === null) { throw new NotFoundException('Car was not found.'); }
        $car->updateRateAndPlate(Money::fromDecimal($command->dailyRate), $command->plateNumber);
        return CarView::fromEntity($this->cars->save($car));
    }
}
