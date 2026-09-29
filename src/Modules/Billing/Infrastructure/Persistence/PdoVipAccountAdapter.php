<?php

declare(strict_types=1);

namespace App\Modules\Billing\Infrastructure\Persistence;

use App\Modules\Billing\Application\Port\VipAccountPort;
use App\Shared\Domain\ValueObject\Money;
use App\Shared\Domain\ValueObject\UserId;
use PDO;

final readonly class PdoVipAccountAdapter implements VipAccountPort
{
    public function __construct(private PDO $pdo) {}

    public function addPoints(UserId $userId, Money $amount): Money
    {
        $update = $this->pdo->prepare('UPDATE accountstable SET vip_points = vip_points + :amount WHERE userID = :user_id');
        $update->execute(['amount' => $amount->toDecimal(), 'user_id' => $userId->toInt()]);
        $select = $this->pdo->prepare('SELECT vip_points FROM accountstable WHERE userID = :user_id');
        $select->execute(['user_id' => $userId->toInt()]);
        return Money::fromDecimal((string) $select->fetchColumn());
    }

    public function grantAccess(UserId $userId): void
    {
        $statement = $this->pdo->prepare('UPDATE accountstable SET vip_access = 1 WHERE userID = :user_id');
        $statement->execute(['user_id' => $userId->toInt()]);
    }
}
