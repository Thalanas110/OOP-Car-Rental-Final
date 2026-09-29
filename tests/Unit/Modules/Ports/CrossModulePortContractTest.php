<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Ports;

use App\Modules\Fleet\Infrastructure\Adapter\PdoCarRateReader;
use App\Modules\Identity\Infrastructure\Adapter\PdoVipStatusReader;
use App\Modules\Users\Infrastructure\Adapter\PdoUserExistenceReader;
use App\Shared\Domain\ValueObject\CarId;
use App\Shared\Domain\ValueObject\Money;
use App\Shared\Domain\ValueObject\UserId;
use PDO;
use PHPUnit\Framework\TestCase;

final class CrossModulePortContractTest extends TestCase
{
    public function testAdaptersExposeOnlyTheirDeclaredPortData(): void
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('CREATE TABLE userstable (userID INTEGER PRIMARY KEY, isdeleted INTEGER NOT NULL)');
        $pdo->exec('CREATE TABLE carstable (carID INTEGER PRIMARY KEY, daily_rate NUMERIC NOT NULL, isdeleted INTEGER NOT NULL)');
        $pdo->exec('CREATE TABLE accountstable (userID INTEGER PRIMARY KEY, vip_points NUMERIC NOT NULL, vip_access INTEGER NOT NULL)');
        $pdo->exec('INSERT INTO userstable VALUES (7, 0)');
        $pdo->exec('INSERT INTO carstable VALUES (2, 1500, 0)');
        $pdo->exec('INSERT INTO accountstable VALUES (7, 500000, 1)');

        self::assertTrue((new PdoUserExistenceReader($pdo))->exists(UserId::fromInt(7)));
        self::assertSame('1500.00', (new PdoCarRateReader($pdo))->rateFor(CarId::fromInt(2))->toDecimal());
        self::assertTrue((new PdoVipStatusReader($pdo, Money::fromDecimal('500000.00')))->isVip(UserId::fromInt(7)));
    }
}
