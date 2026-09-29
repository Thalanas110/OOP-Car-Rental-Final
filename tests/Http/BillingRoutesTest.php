<?php

declare(strict_types=1);

namespace Tests\Http;

use App\Modules\Billing\Application\Command\RecordPaymentHandler;
use App\Modules\Billing\Application\Port\VipAccountPort;
use App\Modules\Billing\Domain\Entity\Payment;
use App\Modules\Billing\Domain\Repository\PaymentRepository;
use App\Modules\Billing\Domain\Service\VipPolicy;
use App\Modules\Billing\Presentation\Http\BillingController;
use App\Shared\Application\Transaction\TransactionManager;
use App\Shared\Domain\ValueObject\BookingId;
use App\Shared\Domain\ValueObject\Money;
use App\Shared\Domain\ValueObject\UserId;
use App\Shared\Presentation\Http\Response\JsonResponder;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;

final class BillingRoutesTest extends TestCase
{
    public function testRecordsPaymentFromLegacyPayload(): void
    {
        $repository = new class implements PaymentRepository {
            public function bookingExists(BookingId $bookingId): bool { return true; }
            public function userIdForBooking(BookingId $bookingId): ?UserId { return UserId::fromInt(7); }
            public function save(Payment $payment): Payment { return $payment; }
            public function totalPaidForUser(UserId $userId): Money { return Money::fromDecimal('3000.00'); }
        };
        $vip = new class implements VipAccountPort { public function addPoints(UserId $userId, Money $amount): Money { return $amount; } public function grantAccess(UserId $userId): void {} };
        $transactions = new class implements TransactionManager { public function run(callable $operation): mixed { return $operation(); } };
        $controller = new BillingController(new RecordPaymentHandler($repository, $vip, new VipPolicy(Money::fromDecimal('500000.00')), $transactions), new JsonResponder());

        $response = $controller->create((new ServerRequest('POST', '/billing'))->withParsedBody(['bookingID' => 12, 'amount_paid' => '3000.00']), new Response());
        $payload = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(201, $response->getStatusCode());
        self::assertSame('3000.00', $payload['data']['amount_paid']);
    }
}
