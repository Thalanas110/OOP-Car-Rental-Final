<?php

declare(strict_types=1);

namespace App\Modules\Identity\Infrastructure\Adapter;

use App\Modules\Identity\Application\Port\VipStatusReader;
use App\Shared\Domain\ValueObject\Money;
use App\Shared\Domain\ValueObject\UserId;
use PDO;

final readonly class PdoVipStatusReader implements VipStatusReader
{
    public function __construct(private PDO $pdo, private Money $threshold) {}
    public function isVip(UserId $userId): bool
    {
        $statement = $this->pdo->prepare('SELECT vip_points, vip_access FROM accountstable WHERE userID = :id LIMIT 1');
        $statement->execute(['id' => $userId->toInt()]);
        $row = $statement->fetch();
        if (!is_array($row)) { return false; }
        $vipAccess = $row['vip_access'] ?? 0;
        $vipPoints = $row['vip_points'] ?? '0';
        return (bool) $vipAccess || (is_string($vipPoints) || is_int($vipPoints) || is_float($vipPoints)) && Money::fromDecimal((string) $vipPoints)->isGreaterThanOrEqualTo($this->threshold);
    }
}
