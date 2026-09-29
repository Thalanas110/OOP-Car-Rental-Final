<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class DateRange
{
    private function __construct(
        public DateTimeImmutable $start,
        public DateTimeImmutable $end,
    ) {
        if ($end < $start) {
            throw new InvalidArgumentException('Date range end must not precede its start.');
        }
    }

    public static function between(DateTimeImmutable $start, DateTimeImmutable $end): self
    {
        return new self($start, $end);
    }

    public function overlaps(self $other): bool
    {
        return $this->start < $other->end && $other->start < $this->end;
    }

    public function billableDays(): int
    {
        $seconds = $this->end->getTimestamp() - $this->start->getTimestamp();

        return max(1, (int) floor($seconds / 86400));
    }
}
