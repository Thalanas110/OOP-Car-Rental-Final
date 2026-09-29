<?php

declare(strict_types=1);

namespace App\Modules\Users\Application\Command;

final readonly class DestroyUser
{
    public function __construct(public int $userId) {}
}
