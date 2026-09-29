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
        if ($row === false) { return false; }
        return (bool) $row['vip_access'] || Money::fromDecimal((string) $row['vip_points'])->isGreaterThanOrEqualTo($this->threshold);
    }
}
