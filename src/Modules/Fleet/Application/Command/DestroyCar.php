<?php

declare(strict_types=1);

namespace App\Modules\Fleet\Application\Command;

final readonly class DestroyCar
{
    public function __construct(public int $carId) {}
}
