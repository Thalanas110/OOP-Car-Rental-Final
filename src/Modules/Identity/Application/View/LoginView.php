<?php

declare(strict_types=1);

namespace App\Modules\Identity\Application\View;

use DateTimeImmutable;

final readonly class LoginView
{
    public function __construct(
        public int $userId,
        public string $email,
        public string $token,
        public DateTimeImmutable $expiresAt,
    ) {
    }

    /** @return array{user_id: int, username: string, token: string, expires_at: string} */
    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'username' => $this->email,
            'token' => $this->token,
            'expires_at' => $this->expiresAt->format(DATE_ATOM),
        ];
    }
}
