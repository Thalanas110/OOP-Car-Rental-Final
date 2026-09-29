<?php

declare(strict_types=1);

namespace App\Modules\Fleet\Infrastructure\Adapter;

use App\Modules\Fleet\Application\Port\CarRateReader;
use App\Shared\Domain\ValueObject\CarId;
use App\Shared\Domain\ValueObject\Money;
use PDO;

final readonly class PdoCarRateReader implements CarRateReader
{
    public function __construct(private PDO $pdo) {}
    public function rateFor(CarId $carId): ?Money
    {
        $statement = $this->pdo->prepare('SELECT daily_rate FROM carstable WHERE carID = :id AND isdeleted = 0 LIMIT 1');
        $statement->execute(['id' => $carId->toInt()]);
        $rate = $statement->fetchColumn();
        return $rate === false ? null : Money::fromDecimal((string) $rate);
    }
}
