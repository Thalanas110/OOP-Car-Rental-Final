<?php

declare(strict_types=1);

namespace App\Modules\Identity\Domain\Entity;

use App\Modules\Identity\Domain\ValueObject\Email;
use App\Modules\Identity\Domain\ValueObject\PasswordHash;
use App\Shared\Domain\ValueObject\UserId;

final readonly class Account
{
    private function __construct(
        private UserId $userId,
        private Email $email,
        private PasswordHash $passwordHash,
    ) {
    }

    public static function register(UserId $userId, Email $email, PasswordHash $passwordHash): self
    {
        return new self($userId, $email, $passwordHash);
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function passwordHash(): PasswordHash
    {
        return $this->passwordHash;
    }
}
