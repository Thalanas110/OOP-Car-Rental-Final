<?php

declare(strict_types=1);

namespace App\Modules\Billing\Application\Command;

use App\Modules\Billing\Application\Port\VipAccountPort;
use App\Modules\Billing\Application\View\PaymentView;
use App\Modules\Billing\Domain\Entity\Payment;
use App\Modules\Billing\Domain\Repository\PaymentRepository;
use App\Modules\Billing\Domain\Service\VipPolicy;
use App\Shared\Application\Transaction\TransactionManager;
use App\Shared\Domain\Exception\NotFoundException;
use App\Shared\Domain\ValueObject\BookingId;
use App\Shared\Domain\ValueObject\Money;

final readonly class RecordPaymentHandler
{
    public function __construct(private PaymentRepository $payments, private VipAccountPort $vipAccounts, private VipPolicy $vipPolicy, private TransactionManager $transactions) {}

    public function __invoke(RecordPayment $command): PaymentView
    {
        $bookingId = BookingId::fromInt($command->bookingId);
        if (!$this->payments->bookingExists($bookingId)) { throw new NotFoundException('Booking was not found.'); }
        $userId = $this->payments->userIdForBooking($bookingId);
        if ($userId === null) { throw new NotFoundException('Booking owner was not found.'); }
        $payment = Payment::record($bookingId, Money::fromDecimal($command->amount));

        return $this->transactions->run(function () use ($payment, $userId): PaymentView {
            $saved = $this->payments->save($payment);
            $points = $this->vipAccounts->addPoints($userId, $saved->amount());
            if ($this->vipPolicy->hasAccess($points)) { $this->vipAccounts->grantAccess($userId); }
            return PaymentView::fromEntity($saved);
        });
    }
}
