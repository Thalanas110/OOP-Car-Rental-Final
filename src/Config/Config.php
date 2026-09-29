<?php

declare(strict_types=1);

namespace App\Config;

final readonly class Config
{
    public function __construct(
        public string $environment,
        private string $dsn,
        public string $databaseUser,
        public string $databasePassword,
        public string $tokenSecret,
        private int $tokenTtl,
        private string $luxuryCarDailyRate,
        private string $vipPointsThreshold,
    ) {
    }

    /** @param array<string, string> $environment */
    public static function fromEnvironment(array $environment): self
    {
        return new self(
            environment: $environment['APP_ENV'] ?? 'production',
            dsn: $environment['DB_DSN'] ?? 'mysql:host=127.0.0.1;dbname=oopapi2;charset=utf8mb4',
            databaseUser: $environment['DB_USER'] ?? 'root',
            databasePassword: $environment['DB_PASSWORD'] ?? '',
            tokenSecret: $environment['JWT_SECRET'] ?? '',
            tokenTtl: (int) ($environment['TOKEN_TTL'] ?? '3600'),
            luxuryCarDailyRate: $environment['LUXURY_CAR_DAILY_RATE'] ?? '200000.00',
            vipPointsThreshold: $environment['VIP_POINTS_THRESHOLD'] ?? '500000.00',
        );
    }

    public function databaseDsn(): string
    {
        return $this->dsn;
    }

    public function tokenTtlSeconds(): int
    {
        return $this->tokenTtl;
    }

    public function luxuryCarDailyRate(): string
    {
        return $this->luxuryCarDailyRate;
    }

    public function vipPointsThreshold(): string
    {
        return $this->vipPointsThreshold;
    }
}
