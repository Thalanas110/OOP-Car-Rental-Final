<?php

declare(strict_types=1);

namespace App\Modules\Identity\Application\Port;

use App\Shared\Domain\ValueObject\UserId;

interface VipStatusReader
{
    public function isVip(UserId $userId): bool;
}
