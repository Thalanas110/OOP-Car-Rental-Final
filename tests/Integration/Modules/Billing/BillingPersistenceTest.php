<?php

declare(strict_types=1);

namespace Tests\Integration\Modules\Billing;

use App\Modules\Billing\Domain\Entity\Payment;
use App\Modules\Billing\Infrastructure\Persistence\PdoPaymentRepository;
use App\Modules\Billing\Infrastructure\Persistence\PdoVipAccountAdapter;
use App\Shared\Domain\ValueObject\BookingId;
use App\Shared\Domain\ValueObject\Money;
use App\Shared\Domain\ValueObject\UserId;
use PDO;
use PHPUnit\Framework\TestCase;

final class BillingPersistenceTest extends TestCase
{
    public function testRecordsPaymentAndPromotesVipPoints(): void
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('CREATE TABLE bookingtable (bookingID INTEGER PRIMARY KEY, userID INTEGER NOT NULL)');
        $pdo->exec('CREATE TABLE billingtable (billingID INTEGER PRIMARY KEY AUTOINCREMENT, bookingID INTEGER NOT NULL, amount_paid NUMERIC NOT NULL)');
        $pdo->exec('CREATE TABLE accountstable (userID INTEGER PRIMARY KEY, vip_points NUMERIC NOT NULL DEFAULT 0, vip_access INTEGER NOT NULL DEFAULT 0)');
        $pdo->exec('INSERT INTO bookingtable VALUES (12, 7)');
        $pdo->exec('INSERT INTO accountstable VALUES (7, 499000, 0)');

        $payments = new PdoPaymentRepository($pdo);
        $vip = new PdoVipAccountAdapter($pdo);
        $payment = $payments->save(Payment::record(BookingId::fromInt(12), Money::fromDecimal('1000.00')));
        $points = $vip->addPoints(UserId::fromInt(7), $payment->amount());
        $vip->grantAccess(UserId::fromInt(7));

        self::assertSame(12, $payment->bookingId()->toInt());
        self::assertSame('500000.00', $points->toDecimal());
        self::assertSame(1, (int) $pdo->query('SELECT vip_access FROM accountstable WHERE userID = 7')->fetchColumn());
    }
}
