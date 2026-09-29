<?php

declare(strict_types=1);

namespace Tests\Integration\Shared;

use App\Config\Config;
use App\Shared\Infrastructure\Persistence\PdoConnectionFactory;
use App\Shared\Infrastructure\Persistence\PdoTransactionManager;
use PDO;
use PHPUnit\Framework\TestCase;

final class PdoTransactionTest extends TestCase
{
    public function testCommitsSuccessfulCallback(): void
    {
        $pdo = $this->connection();
        $pdo->exec('CREATE TABLE events (id INTEGER PRIMARY KEY, name TEXT NOT NULL)');
        $transactions = new PdoTransactionManager($pdo);

        $transactions->run(static function () use ($pdo): void {
            $pdo->exec("INSERT INTO events (id, name) VALUES (1, 'created')");
        });

        self::assertSame(1, (int) $pdo->query('SELECT COUNT(*) FROM events')->fetchColumn());
    }

    public function testRollsBackWhenCallbackThrows(): void
    {
        $pdo = $this->connection();
        $pdo->exec('CREATE TABLE events (id INTEGER PRIMARY KEY, name TEXT NOT NULL)');
        $transactions = new PdoTransactionManager($pdo);

        try {
            $transactions->run(static function () use ($pdo): void {
                $pdo->exec("INSERT INTO events (id, name) VALUES (1, 'rolled-back')");
                throw new \RuntimeException('fail');
            });
        } catch (\RuntimeException) {
        }

        self::assertSame(0, (int) $pdo->query('SELECT COUNT(*) FROM events')->fetchColumn());
    }

    private function connection(): PDO
    {
        return new PdoConnectionFactory()->create(Config::fromEnvironment([
            'DB_DSN' => 'sqlite::memory:',
            'DB_USER' => '',
            'DB_PASSWORD' => '',
        ]));
    }
}
