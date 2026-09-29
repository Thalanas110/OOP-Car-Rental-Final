<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Billing;

use App\Modules\Billing\Domain\Entity\Payment;
use App\Modules\Billing\Domain\Service\VipPolicy;
use App\Shared\Domain\ValueObject\BookingId;
use App\Shared\Domain\ValueObject\Money;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class VipPolicyTest extends TestCase
{
    public function testAddsPaymentAmountToVipPointsAndChecksThreshold(): void
    {
        $policy = new VipPolicy(Money::fromDecimal('500000.00'));
        $points = $policy->pointsAfter(Money::fromDecimal('499000.00'), Money::fromDecimal('1000.00'));

        self::assertSame('500000.00', $points->toDecimal());
        self::assertTrue($policy->hasAccess($points));
    }

    public function testRecordsPositivePayment(): void
    {
        $payment = Payment::record(BookingId::fromInt(12), Money::fromDecimal('3000.00'));

        self::assertSame(12, $payment->bookingId()->toInt());
        self::assertSame('3000.00', $payment->amount()->toDecimal());
    }

    public function testRejectsZeroPayment(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Payment::record(BookingId::fromInt(12), Money::fromDecimal('0.00'));
    }
}
