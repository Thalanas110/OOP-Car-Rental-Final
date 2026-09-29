<?php

declare(strict_types=1);

namespace App\Modules\Identity\Application\Command;

use App\Modules\Identity\Application\Security\PasswordHasher;
use App\Modules\Identity\Application\View\AccountView;
use App\Modules\Identity\Domain\Entity\Account;
use App\Modules\Identity\Domain\Repository\AccountRepository;
use App\Modules\Identity\Domain\ValueObject\Email;
use App\Modules\Identity\Domain\ValueObject\PasswordHash;
use App\Shared\Domain\Exception\ConflictException;
use App\Shared\Domain\ValueObject\UserId;

final readonly class RegisterAccountHandler
{
    public function __construct(
        private AccountRepository $accounts,
        private PasswordHasher $passwords,
    ) {
    }

    public function __invoke(RegisterAccount $command): AccountView
    {
        $email = Email::fromString($command->email);
        if ($this->accounts->findByEmail($email) !== null) {
            throw new ConflictException('Email address is already registered.');
        }

        $account = Account::register(
            UserId::fromInt($command->userId),
            $email,
            PasswordHash::fromString($this->passwords->hash($command->password)),
        );
        $this->accounts->save($account);

        return new AccountView($account->userId()->toInt(), $account->email()->toString());
    }
}
