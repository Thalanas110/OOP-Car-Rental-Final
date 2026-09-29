<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Http\Response;

final readonly class ResponseEnvelope
{
    /** @param array<string, mixed>|list<mixed>|null $data */
    private function __construct(
        private ?array $data,
        private ?array $error,
        private array $meta,
    ) {
    }

    /** @param array<string, mixed>|list<mixed>|null $data */
    public static function success(?array $data, array $meta): self
    {
        return new self($data, null, $meta);
    }

    /** @param array<string, mixed> $errorResponse */
    public static function error(array $errorResponse): self
    {
        return new self(null, $errorResponse['error'] ?? null, $errorResponse['meta'] ?? []);
    }

    /** @return array{data: array<string, mixed>|list<mixed>|null, error: array<string, mixed>|null, meta: array<string, mixed>} */
    public function toArray(): array
    {
        return ['data' => $this->data, 'error' => $this->error, 'meta' => $this->meta];
    }
}
