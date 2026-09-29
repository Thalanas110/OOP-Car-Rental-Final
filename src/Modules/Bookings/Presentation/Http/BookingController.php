<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Presentation\Http;

use App\Modules\Bookings\Application\Command\CreateBooking;
use App\Modules\Bookings\Application\Command\CreateBookingHandler;
use App\Shared\Domain\Exception\ValidationException;
use App\Shared\Presentation\Http\Response\JsonResponder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class BookingController
{
    public function __construct(private CreateBookingHandler $createBooking, private JsonResponder $responder) {}

    public function create(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body = $request->getParsedBody();
        if (!is_array($body)) { throw new ValidationException('Request body must be a JSON object.'); }
        $view = ($this->createBooking)(new CreateBooking($this->positiveInt($body, 'carID'), $this->positiveInt($body, 'userID'), $this->string($body, 'book_date'), $this->string($body, 'return_date')));
        return $this->responder->success($view->toArray(), 201, ['request_id' => (string) ($request->getAttribute('request_id') ?? 'unknown')]);
    }

    /** @param array<string, mixed> $body */
    private function positiveInt(array $body, string $key): int
    {
        $value = $body[$key] ?? null;
        if (!is_int($value) && !(is_string($value) && ctype_digit($value))) { throw new ValidationException(sprintf('%s must be a positive integer.', $key)); }
        $integer = (int) $value;
        if ($integer < 1) { throw new ValidationException(sprintf('%s must be a positive integer.', $key)); }
        return $integer;
    }

    /** @param array<string, mixed> $body */
    private function string(array $body, string $key): string
    {
        $value = $body[$key] ?? null;
        if (!is_string($value) || trim($value) === '') { throw new ValidationException(sprintf('%s is required.', $key)); }
        return $value;
    }
}
