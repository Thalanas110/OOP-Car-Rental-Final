<?php

declare(strict_types=1);

namespace App\Modules\Fleet\Application\Handler;

use App\Modules\Fleet\Application\Query\ListCars;
use App\Modules\Fleet\Application\View\CarView;
use App\Modules\Fleet\Domain\Repository\CarRepository;

final readonly class ListCarsHandler
{
    public function __construct(private CarRepository $cars) {}
    /** @return list<CarView> */
    public function __invoke(ListCars $query): array
    {
        return array_map(static fn($car): CarView => CarView::fromEntity($car), $this->cars->listActive($query->includeLuxury));
    }
}
