<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Http\Response;

use JsonException;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

final class JsonResponder
{
    /**
     * @param array<string, mixed>|list<mixed>|null $data
     * @param array<string, mixed> $meta
     */
    public function success(?array $data, int $status, array $meta = []): ResponseInterface
    {
        return $this->response(ResponseEnvelope::success($data, $meta)->toArray(), $status);
    }

    /** @param array<string, mixed> $errorResponse */
    public function error(array $errorResponse): ResponseInterface
    {
        $status = $errorResponse['status'] ?? 500;
        return $this->response(ResponseEnvelope::error($errorResponse)->toArray(), is_int($status) ? $status : 500);
    }

    /** @param array<string, mixed> $payload */
    private function response(array $payload, int $status): ResponseInterface
    {
        try {
            $body = json_encode($payload, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new \RuntimeException('Unable to serialize JSON response.', 0, $exception);
        }

        return new Response($status, ['Content-Type' => 'application/json'], $body);
    }
}
