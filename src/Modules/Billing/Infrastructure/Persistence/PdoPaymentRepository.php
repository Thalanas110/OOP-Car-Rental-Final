<?php

declare(strict_types=1);

namespace App\Modules\Billing\Infrastructure\Persistence;

use App\Modules\Billing\Domain\Entity\Payment;
use App\Modules\Billing\Domain\Repository\PaymentRepository;
use App\Shared\Domain\ValueObject\BookingId;
use App\Shared\Domain\ValueObject\Money;
use App\Shared\Domain\ValueObject\UserId;
use PDO;

final readonly class PdoPaymentRepository implements PaymentRepository
{
    public function __construct(private PDO $pdo) {}

    public function bookingExists(BookingId $bookingId): bool
    {
        $statement = $this->pdo->prepare('SELECT 1 FROM bookingtable WHERE bookingID = :id LIMIT 1');
        $statement->execute(['id' => $bookingId->toInt()]);
        return $statement->fetchColumn() !== false;
    }

    public function userIdForBooking(BookingId $bookingId): ?UserId
    {
        $statement = $this->pdo->prepare('SELECT userID FROM bookingtable WHERE bookingID = :id LIMIT 1');
        $statement->execute(['id' => $bookingId->toInt()]);
        $userId = $statement->fetchColumn();
        return $userId === false ? null : UserId::fromInt((int) $userId);
    }

    public function save(Payment $payment): Payment
    {
        $statement = $this->pdo->prepare('INSERT INTO billingtable (bookingID, amount_paid) VALUES (:booking_id, :amount)');
        $statement->execute(['booking_id' => $payment->bookingId()->toInt(), 'amount' => $payment->amount()->toDecimal()]);
        return $payment;
    }

    public function totalPaidForUser(UserId $userId): Money
    {
        $statement = $this->pdo->prepare('SELECT COALESCE(SUM(b.amount_paid), 0) FROM billingtable b INNER JOIN bookingtable bk ON bk.bookingID = b.bookingID WHERE bk.userID = :user_id');
        $statement->execute(['user_id' => $userId->toInt()]);
        return Money::fromDecimal((string) $statement->fetchColumn());
    }
}
