<?php

declare(strict_types=1);

namespace App\Modules\Identity\Application\Security;

use App\Config\Config;
use App\Modules\Identity\Domain\Repository\AccountRepository;
use App\Modules\Identity\Domain\ValueObject\Email;
use App\Shared\Domain\Service\Clock;
use App\Shared\Domain\ValueObject\UserId;

final readonly class OpaqueTokenIssuer implements TokenIssuer, TokenVerifier
{
    public function __construct(
        private AccountRepository $accounts,
        private Clock $clock,
        private Config $config,
    ) {}

    public function issue(UserId $userId, Email $email): Token
    {
        $expiresAt = $this->clock->now()->modify(sprintf('+%d seconds', $this->config->tokenTtlSeconds()));
        $token = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $this->accounts->replaceToken($userId, $token, $expiresAt);

        return new Token($token, $expiresAt);
    }

    public function verify(string $token): ?UserId
    {
        if ($token === '') {
            return null;
        }

        return $this->accounts->findUserIdByToken($token, $this->clock->now());
    }
}
