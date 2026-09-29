<?php

declare(strict_types=1);

namespace App\Modules\Users\Domain\ValueObject;

use InvalidArgumentException;

final readonly class DriverLicense
{
    private function __construct(private string $value)
    {
        if ($value === '') {
            throw new InvalidArgumentException('Driver license is required.');
        }
    }

    public static function fromString(string $value): self
    {
        return new self(strtoupper(trim($value)));
    }

    public function toString(): string
    {
        return $this->value;
    }
}
