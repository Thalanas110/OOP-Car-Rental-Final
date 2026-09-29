<?php

declare(strict_types=1);

namespace App\Modules\Fleet\Application\Handler;

use App\Modules\Fleet\Application\Query\GetCar;
use App\Modules\Fleet\Application\View\CarView;
use App\Modules\Fleet\Domain\Repository\CarRepository;
use App\Shared\Domain\Exception\NotFoundException;
use App\Shared\Domain\ValueObject\CarId;

final readonly class GetCarHandler
{
    public function __construct(private CarRepository $cars) {}
    public function __invoke(GetCar $query): CarView
    {
        $car = $this->cars->find(CarId::fromInt($query->carId));
        if ($car === null) { throw new NotFoundException('Car was not found.'); }
        return CarView::fromEntity($car);
    }
}
