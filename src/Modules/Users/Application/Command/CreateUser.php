<?php

declare(strict_types=1);

namespace App\Modules\Users\Application\Command;

final readonly class CreateUser
{
    public function __construct(public string $name, public string $contactNumber, public string $driversLicense) {}
}
