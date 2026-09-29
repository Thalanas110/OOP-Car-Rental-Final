<?php

declare(strict_types=1);

namespace App\Modules\Fleet\Application\Query;

final readonly class CarChecking
{
    public function __construct(public bool $includeLuxury = false) {}
}
