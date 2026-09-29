<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

use InvalidArgumentException;

abstract readonly class Identifier
{
    protected function __construct(private int $value)
    {
        if ($value < 1) {
            throw new InvalidArgumentException('Identifier must be a positive integer.');
        }
    }

    public static function fromInt(int $value): static
    {
        return new static($value);
    }

    public function toInt(): int
    {
        return $this->value;
    }
}
