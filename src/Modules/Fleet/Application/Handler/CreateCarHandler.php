<?php

declare(strict_types=1);

namespace App\Modules\Fleet\Application\Handler;

use App\Modules\Fleet\Application\Command\CreateCar;
use App\Modules\Fleet\Application\View\CarView;
use App\Modules\Fleet\Domain\Entity\Car;
use App\Modules\Fleet\Domain\Repository\CarRepository;
use App\Shared\Domain\ValueObject\Money;

final readonly class CreateCarHandler
{
    public function __construct(private CarRepository $cars) {}
    public function __invoke(CreateCar $command): CarView
    {
        return CarView::fromEntity($this->cars->save(Car::register($command->brand, $command->model, $command->manufactureYear, Money::fromDecimal($command->dailyRate), $command->airConditioned, $command->seatingCapacity, $command->plateNumber)));
    }
}
