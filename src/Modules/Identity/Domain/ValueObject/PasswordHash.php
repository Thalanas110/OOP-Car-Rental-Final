<?php

declare(strict_types=1);

namespace App\Modules\Identity\Domain\ValueObject;

use InvalidArgumentException;

final readonly class PasswordHash
{
    private function __construct(private string $value)
    {
        if ($value === '') {
            throw new InvalidArgumentException('Password hash cannot be empty.');
        }
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function toString(): string
    {
        return $this->value;
    }
}
