<?php

declare(strict_types=1);

namespace App\Modules\Identity\Application\Command;

use App\Modules\Identity\Application\Security\PasswordHasher;
use App\Modules\Identity\Application\Security\TokenIssuer;
use App\Modules\Identity\Application\View\LoginView;
use App\Modules\Identity\Domain\Repository\AccountRepository;
use App\Modules\Identity\Domain\ValueObject\Email;
use App\Shared\Domain\Exception\UnauthorizedException;

final readonly class LoginHandler
{
    public function __construct(
        private AccountRepository $accounts,
        private PasswordHasher $passwords,
        private TokenIssuer $tokens,
    ) {
    }

    public function __invoke(Login $command): LoginView
    {
        $email = Email::fromString($command->email);
        $account = $this->accounts->findByEmail($email);
        if ($account === null || !$this->passwords->verify($command->password, $account->passwordHash()->toString())) {
            throw new UnauthorizedException();
        }

        $token = $this->tokens->issue($account->userId(), $account->email());

        return new LoginView($account->userId()->toInt(), $account->email()->toString(), $token->value, $token->expiresAt);
    }
}
