<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Http\Response;

final readonly class ResponseEnvelope
{
    /**
     * @param array<string, mixed>|list<mixed>|null $data
     * @param array<string, mixed>|null $error
     * @param array<string, mixed> $meta
     */
    private function __construct(
        private ?array $data,
        private ?array $error,
        private array $meta,
    ) {}

    /**
     * @param array<string, mixed>|list<mixed>|null $data
     * @param array<string, mixed> $meta
     */
    public static function success(?array $data, array $meta): self
    {
        return new self($data, null, $meta);
    }

    /** @param array<string, mixed> $errorResponse */
    public static function error(array $errorResponse): self
    {
        return new self(null, self::mapOrNull($errorResponse['error'] ?? null), self::map($errorResponse['meta'] ?? []));
    }

    /** @return array<string, mixed>|null */
    private static function mapOrNull(mixed $value): ?array
    {
        return $value === null ? null : self::map($value);
    }

    /** @return array<string, mixed> */
    private static function map(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $map = [];
        foreach ($value as $key => $item) {
            if (is_string($key)) {
                $map[$key] = $item;
            }
        }

        return $map;
    }

    /** @return array{data: array<string, mixed>|list<mixed>|null, error: array<string, mixed>|null, meta: array<string, mixed>} */
    public function toArray(): array
    {
        return ['data' => $this->data, 'error' => $this->error, 'meta' => $this->meta];
    }
}
