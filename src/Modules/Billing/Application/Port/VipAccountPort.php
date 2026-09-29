<?php

declare(strict_types=1);

namespace App\Modules\Billing\Application\Port;

use App\Shared\Domain\ValueObject\Money;
use App\Shared\Domain\ValueObject\UserId;

interface VipAccountPort
{
    public function addPoints(UserId $userId, Money $amount): Money;
    public function grantAccess(UserId $userId): void;
}
