<?php

declare(strict_types=1);

namespace App\Modules\Users\Application\View;

use App\Modules\Users\Domain\Entity\User;

final readonly class UserView
{
    public function __construct(
        public int $userId,
        public string $name,
        public string $contactNumber,
        public string $driversLicense,
        public bool $archived,
    ) {
    }

    public static function fromEntity(User $user): self
    {
        return new self($user->id()?->toInt() ?? 0, $user->name(), $user->contactNumber(), $user->driversLicense()->toString(), $user->isArchived());
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return ['userID' => $this->userId, 'name' => $this->name, 'contact_no' => $this->contactNumber, 'drivers_license' => $this->driversLicense, 'isdeleted' => $this->archived];
    }
}
