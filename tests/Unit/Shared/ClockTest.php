<?php

declare(strict_types=1);

namespace Tests\Unit\Shared;

use App\Shared\Application\Transaction\TransactionManager;
use App\Shared\Infrastructure\Time\FrozenClock;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class ClockTest extends TestCase
{
    public function testFrozenClockReturnsConfiguredTime(): void
    {
        $clock = new FrozenClock(new DateTimeImmutable('2026-09-29 12:00:00 UTC'));

        self::assertSame('2026-09-29 12:00:00', $clock->now()->format('Y-m-d H:i:s'));
    }

    public function testTransactionPortDescribesCallbackBoundary(): void
    {
        $manager = new class implements TransactionManager {
            public function run(callable $operation): mixed
            {
                return $operation();
            }
        };

        self::assertSame('completed', $manager->run(static fn (): string => 'completed'));
    }
}
