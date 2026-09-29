<?php

declare(strict_types=1);

namespace App\Modules\Fleet\Application\Handler;

use App\Modules\Fleet\Application\Command\DestroyCar;
use App\Modules\Fleet\Domain\Repository\CarRepository;
use App\Shared\Domain\ValueObject\CarId;

final readonly class DestroyCarHandler
{
    public function __construct(private CarRepository $cars) {}
    public function __invoke(DestroyCar $command): void
    {
        $this->cars->delete(CarId::fromInt($command->carId));
    }
}
