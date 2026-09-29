<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

use InvalidArgumentException;

final readonly class Money
{
    private function __construct(private int $minorUnits)
    {
        if ($minorUnits < 0) {
            throw new InvalidArgumentException('Money cannot be negative.');
        }
    }

    public static function fromDecimal(string $decimal): self
    {
        if (!preg_match('/^\d+(?:\.\d{1,2})?$/', $decimal)) {
            throw new InvalidArgumentException('Money must be a non-negative decimal with up to two digits.');
        }

        [$whole, $fraction] = array_pad(explode('.', $decimal, 2), 2, '0');

        return new self(((int) $whole * 100) + (int) str_pad($fraction, 2, '0'));
    }

    public static function fromMinorUnits(int $minorUnits): self
    {
        return new self($minorUnits);
    }

    public function plus(self $other): self
    {
        return new self($this->minorUnits + $other->minorUnits);
    }

    public function multipliedBy(int $factor): self
    {
        if ($factor < 0) {
            throw new InvalidArgumentException('Money cannot be multiplied by a negative factor.');
        }

        return new self($this->minorUnits * $factor);
    }

    public function isGreaterThanOrEqualTo(self $other): bool
    {
        return $this->minorUnits >= $other->minorUnits;
    }

    public function toDecimal(): string
    {
        return number_format($this->minorUnits / 100, 2, '.', '');
    }

    public function toMinorUnits(): int
    {
        return $this->minorUnits;
    }
}
