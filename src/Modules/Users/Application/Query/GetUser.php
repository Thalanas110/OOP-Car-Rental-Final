<?php

declare(strict_types=1);

namespace App\Modules\Users\Application\Query;

final readonly class GetUser
{
    public function __construct(public int $userId) {}
}
