<?php

declare(strict_types=1);

namespace Tests\Http;

use App\Modules\Bookings\Application\Command\CreateBookingHandler;
use App\Modules\Bookings\Domain\Repository\BookingRepository;
use App\Modules\Bookings\Domain\Service\BookingPolicy;
use App\Modules\Bookings\Presentation\Http\BookingController;
use App\Modules\Fleet\Application\Port\CarRateReader;
use App\Modules\Identity\Application\Port\VipStatusReader;
use App\Modules\Users\Application\Port\UserExistenceReader;
use App\Shared\Application\Transaction\TransactionManager;
use App\Shared\Domain\ValueObject\BookingId;
use App\Shared\Domain\ValueObject\CarId;
use App\Shared\Domain\ValueObject\DateRange;
use App\Shared\Domain\ValueObject\Money;
use App\Shared\Domain\ValueObject\UserId;
use App\Modules\Bookings\Domain\Entity\Booking;
use App\Shared\Presentation\Http\Response\JsonResponder;
use DateTimeImmutable;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;

final class BookingRoutesTest extends TestCase
{
    public function testCreatesBookingFromLegacyPayload(): void
    {
        $repository = new class implements BookingRepository {
            public function hasOverlap(CarId $carId, DateRange $dateRange): bool
            {
                return false;
            }
            public function save(Booking $booking): Booking
            {
                return Booking::reconstitute(BookingId::fromInt(1), $booking->carId(), $booking->userId(), $booking->dateRange(), $booking->dailyRate(), $booking->totalCost());
            }
            public function find(BookingId $id): ?Booking
            {
                return null;
            }
        };
        $users = new class implements UserExistenceReader {
            public function exists(UserId $userId): bool
            {
                return true;
            }
        };
        $cars = new class implements CarRateReader {
            public function rateFor(CarId $carId): ?Money
            {
                return Money::fromDecimal('1500.00');
            }
        };
        $vip = new class implements VipStatusReader {
            public function isVip(UserId $userId): bool
            {
                return false;
            }
        };
        $transactions = new class implements TransactionManager {
            public function run(callable $operation): mixed
            {
                return $operation();
            }
        };
        $handler = new CreateBookingHandler($repository, $users, $cars, $vip, new BookingPolicy(), $transactions, Money::fromDecimal('200000.00'));
        $controller = new BookingController($handler, new JsonResponder());

        $response = $controller->create((new ServerRequest('POST', '/carbooking'))->withParsedBody(['carID' => 2, 'userID' => 7, 'book_date' => '2026-10-01', 'return_date' => '2026-10-04']), new Response());
        $payload = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(201, $response->getStatusCode());
        self::assertSame('4500.00', $payload['data']['total_cost']);
    }
}
