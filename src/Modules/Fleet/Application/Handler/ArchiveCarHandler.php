<?php

declare(strict_types=1);

namespace App\Modules\Fleet\Application\Handler;

use App\Modules\Fleet\Application\Command\ArchiveCar;
use App\Modules\Fleet\Domain\Repository\CarRepository;
use App\Shared\Domain\ValueObject\CarId;

final readonly class ArchiveCarHandler
{
    public function __construct(private CarRepository $cars) {}
    public function __invoke(ArchiveCar $command): void
    {
        $this->cars->archive(CarId::fromInt($command->carId));
    }
}
