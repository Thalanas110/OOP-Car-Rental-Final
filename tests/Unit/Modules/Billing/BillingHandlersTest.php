<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Billing;

use App\Modules\Billing\Application\Command\RecordPayment;
use App\Modules\Billing\Application\Command\RecordPaymentHandler;
use App\Modules\Billing\Application\Port\VipAccountPort;
use App\Modules\Billing\Domain\Entity\Payment;
use App\Modules\Billing\Domain\Repository\PaymentRepository;
use App\Modules\Billing\Domain\Service\VipPolicy;
use App\Modules\Billing\Application\Query\GetTotalPaid;
use App\Modules\Billing\Application\Query\GetTotalPaidHandler;
use App\Shared\Application\Transaction\TransactionManager;
use App\Shared\Domain\Exception\NotFoundException;
use App\Shared\Domain\ValueObject\BookingId;
use App\Shared\Domain\ValueObject\Money;
use App\Shared\Domain\ValueObject\UserId;
use PHPUnit\Framework\TestCase;

final class BillingHandlersTest extends TestCase
{
    public function testRecordsPaymentAndGrantsVipAccessAtThreshold(): void
    {
        $repository = new class implements PaymentRepository {
            public function bookingExists(BookingId $bookingId): bool
            {
                return true;
            }
            public function userIdForBooking(BookingId $bookingId): ?UserId
            {
                return UserId::fromInt(7);
            }
            public function save(Payment $payment): Payment
            {
                return $payment;
            }
            public function totalPaidForUser(UserId $userId): Money
            {
                return Money::fromDecimal('500000.00');
            }
        };
        $vip = new class implements VipAccountPort {
            public bool $granted = false;
            public function addPoints(UserId $userId, Money $amount): Money
            {
                return Money::fromDecimal('500000.00');
            }
            public function grantAccess(UserId $userId): void
            {
                $this->granted = true;
            }
        };
        $transactions = new class implements TransactionManager {
            public function run(callable $operation): mixed
            {
                return $operation();
            }
        };

        $handler = new RecordPaymentHandler($repository, $vip, new VipPolicy(Money::fromDecimal('500000.00')), $transactions);
        $view = $handler(new RecordPayment(12, '1000.00'));

        self::assertSame(12, $view->bookingId);
        self::assertSame('1000.00', $view->amount);
        self::assertTrue($vip->granted);
    }

    public function testRejectsPaymentForMissingBooking(): void
    {
        $repository = new class implements PaymentRepository {
            public function bookingExists(BookingId $bookingId): bool
            {
                return false;
            }
            public function userIdForBooking(BookingId $bookingId): ?UserId
            {
                return null;
            }
            public function save(Payment $payment): Payment
            {
                return $payment;
            }
            public function totalPaidForUser(UserId $userId): Money
            {
                return Money::fromDecimal('0.00');
            }
        };
        $vip = new class implements VipAccountPort {
            public function addPoints(UserId $userId, Money $amount): Money
            {
                return $amount;
            } public function grantAccess(UserId $userId): void {}
        };
        $transactions = new class implements TransactionManager {
            public function run(callable $operation): mixed
            {
                return $operation();
            }
        };

        $this->expectException(NotFoundException::class);
        (new RecordPaymentHandler($repository, $vip, new VipPolicy(Money::fromDecimal('500000.00')), $transactions))(new RecordPayment(12, '1000.00'));
    }

    public function testReadsTotalPaid(): void
    {
        $repository = new class implements PaymentRepository {
            public function bookingExists(BookingId $bookingId): bool
            {
                return true;
            }
            public function userIdForBooking(BookingId $bookingId): ?UserId
            {
                return UserId::fromInt(7);
            }
            public function save(Payment $payment): Payment
            {
                return $payment;
            }
            public function totalPaidForUser(UserId $userId): Money
            {
                return Money::fromDecimal('3000.00');
            }
        };

        self::assertSame('3000.00', (new GetTotalPaidHandler($repository))(new GetTotalPaid(7))->totalPaid);
    }
}
