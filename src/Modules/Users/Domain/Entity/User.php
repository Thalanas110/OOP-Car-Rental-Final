<?php

declare(strict_types=1);

namespace App\Modules\Users\Domain\Entity;

use App\Modules\Users\Domain\ValueObject\DriverLicense;
use App\Shared\Domain\ValueObject\UserId;
use InvalidArgumentException;

final class User
{
    private function __construct(
        private ?UserId $id,
        private string $name,
        private string $contactNumber,
        private DriverLicense $driversLicense,
        private bool $archived,
    ) {
        if (trim($name) === '' || trim($contactNumber) === '') {
            throw new InvalidArgumentException('User name and contact number are required.');
        }
    }

    public static function register(string $name, string $contactNumber, DriverLicense $driversLicense): self
    {
        return new self(null, trim($name), trim($contactNumber), $driversLicense, false);
    }

    public static function reconstitute(UserId $id, string $name, string $contactNumber, DriverLicense $license, bool $archived): self
    {
        return new self($id, $name, $contactNumber, $license, $archived);
    }

    public function assignId(UserId $id): void
    {
        $this->id = $id;
    }
    public function id(): ?UserId
    {
        return $this->id;
    }
    public function name(): string
    {
        return $this->name;
    }
    public function contactNumber(): string
    {
        return $this->contactNumber;
    }
    public function driversLicense(): DriverLicense
    {
        return $this->driversLicense;
    }
    public function isArchived(): bool
    {
        return $this->archived;
    }

    public function updateProfile(string $name, string $contactNumber, DriverLicense $driversLicense): void
    {
        if (trim($name) === '' || trim($contactNumber) === '') {
            throw new InvalidArgumentException('User name and contact number are required.');
        }

        $this->name = trim($name);
        $this->contactNumber = trim($contactNumber);
        $this->driversLicense = $driversLicense;
    }

    public function archive(): void
    {
        $this->archived = true;
    }
}
