<?php

declare(strict_types=1);

namespace App\Modules\Fleet\Application\Query;

final readonly class GetCar
{
    public function __construct(public int $carId) {}
}
