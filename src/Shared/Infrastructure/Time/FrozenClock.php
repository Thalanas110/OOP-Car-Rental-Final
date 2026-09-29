<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Time;

use App\Shared\Domain\Service\Clock;
use DateTimeImmutable;

final readonly class FrozenClock implements Clock
{
    public function __construct(private DateTimeImmutable $time) {}

    public function now(): DateTimeImmutable
    {
        return $this->time;
    }
}
