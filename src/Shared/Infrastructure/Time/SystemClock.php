<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Time;

use App\Shared\Domain\Service\Clock;
use DateTimeImmutable;
use DateTimeZone;

final class SystemClock implements Clock
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', new DateTimeZone('UTC'));
    }
}
