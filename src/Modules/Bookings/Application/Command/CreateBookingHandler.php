<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Application\Command;

use App\Modules\Bookings\Application\View\BookingView;
use App\Modules\Bookings\Domain\Entity\Booking;
use App\Modules\Bookings\Domain\Repository\BookingRepository;
use App\Modules\Bookings\Domain\Service\BookingPolicy;
use App\Modules\Fleet\Application\Port\CarRateReader;
use App\Modules\Identity\Application\Port\VipStatusReader;
use App\Modules\Users\Application\Port\UserExistenceReader;
use App\Shared\Application\Transaction\TransactionManager;
use App\Shared\Domain\Exception\ValidationException;
use App\Shared\Domain\Exception\NotFoundException;
use App\Shared\Domain\ValueObject\CarId;
use App\Shared\Domain\ValueObject\DateRange;
use App\Shared\Domain\ValueObject\Money;
use App\Shared\Domain\ValueObject\UserId;
use DateTimeImmutable;
use Exception;

final readonly class CreateBookingHandler
{
    public function __construct(private BookingRepository $bookings, private UserExistenceReader $users, private CarRateReader $cars, private VipStatusReader $vipStatus, private BookingPolicy $policy, private TransactionManager $transactions, private Money $luxuryThreshold) {}

    public function __invoke(CreateBooking $command): BookingView
    {
        try {
            $range = DateRange::between(new DateTimeImmutable($command->bookDate), new DateTimeImmutable($command->returnDate));
        } catch (Exception $exception) {
            throw new ValidationException('Booking dates must be valid.');
        }

        return $this->transactions->run(function () use ($command, $range): BookingView {
            $userId = UserId::fromInt($command->userId);
            $carId = CarId::fromInt($command->carId);
            $rate = $this->cars->rateFor($carId);
            $userExists = $this->users->exists($userId);
            $carExists = $rate !== null;
            if ($rate === null) {
                throw new NotFoundException('Car was not found.');
            }
            $luxury = $rate->isGreaterThanOrEqualTo($this->luxuryThreshold);
            $this->policy->assertBookable($userExists, $carExists, $luxury, $this->vipStatus->isVip($userId), $this->bookings->hasOverlap($carId, $range), $range);

            return BookingView::fromEntity($this->bookings->save(Booking::create($carId, $userId, $range, $rate)));
        });
    }
}
