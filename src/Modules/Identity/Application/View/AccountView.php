<?php

declare(strict_types=1);

namespace App\Modules\Identity\Application\View;

final readonly class AccountView
{
    public function __construct(
        public int $userId,
        public string $email,
    ) {}

    /** @return array{user_id: int, email: string} */
    public function toArray(): array
    {
        return ['user_id' => $this->userId, 'email' => $this->email];
    }
}
