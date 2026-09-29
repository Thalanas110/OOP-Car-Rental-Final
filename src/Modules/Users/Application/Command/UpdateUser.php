<?php

declare(strict_types=1);

namespace App\Modules\Users\Application\Command;

final readonly class UpdateUser
{
    public function __construct(public int $userId, public string $name, public string $contactNumber, public string $driversLicense) {}
}
