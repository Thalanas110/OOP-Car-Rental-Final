<?php

declare(strict_types=1);

namespace App\Shared\Domain\Service;

use DateTimeImmutable;

interface Clock
{
    public function now(): DateTimeImmutable;
}
