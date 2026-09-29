<?php

declare(strict_types=1);

namespace App\Modules\Billing\Presentation\Http;

use App\Modules\Billing\Application\Command\RecordPayment;
use App\Modules\Billing\Application\Command\RecordPaymentHandler;
use App\Shared\Domain\Exception\ValidationException;
use App\Shared\Presentation\Http\Response\JsonResponder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class BillingController
{
    public function __construct(private RecordPaymentHandler $recordPayment, private JsonResponder $responder) {}

    public function create(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body = $request->getParsedBody();
        if (!is_array($body)) { throw new ValidationException('Request body must be a JSON object.'); }
        $bookingId = $body['bookingID'] ?? null;
        $amount = $body['amount_paid'] ?? null;
        if ((!is_int($bookingId) && !(is_string($bookingId) && ctype_digit($bookingId))) || (int) $bookingId < 1) { throw new ValidationException('bookingID must be a positive integer.'); }
        if (!is_string($amount) || trim($amount) === '') { throw new ValidationException('amount_paid is required.'); }
        $view = ($this->recordPayment)(new RecordPayment((int) $bookingId, $amount));
        return $this->responder->success($view->toArray(), 201, ['request_id' => (string) ($request->getAttribute('request_id') ?? 'unknown')]);
    }
}
