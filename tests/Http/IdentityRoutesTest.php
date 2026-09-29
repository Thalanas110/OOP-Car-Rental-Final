<?php

declare(strict_types=1);

namespace Tests\Http;

use App\Modules\Identity\Application\Command\LoginHandler;
use App\Modules\Identity\Application\Command\RegisterAccountHandler;
use App\Modules\Identity\Application\Security\NativePasswordHasher;
use App\Modules\Identity\Application\Security\Token;
use App\Modules\Identity\Application\Security\TokenIssuer;
use App\Modules\Identity\Domain\Entity\Account;
use App\Modules\Identity\Domain\Repository\AccountRepository;
use App\Modules\Identity\Domain\ValueObject\Email;
use App\Modules\Identity\Domain\ValueObject\PasswordHash;
use App\Modules\Identity\Presentation\Http\IdentityController;
use App\Shared\Domain\ValueObject\UserId;
use DateTimeImmutable;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;

final class IdentityRoutesTest extends TestCase
{
    public function testLoginControllerReturnsDocumentedPayload(): void
    {
        $repository = new class implements AccountRepository {
            private Account $account;
            public function __construct()
            {
                $this->account = Account::register(UserId::fromInt(7), Email::fromString('customer@example.com'), PasswordHash::fromString(password_hash('secret', PASSWORD_DEFAULT)));
            }
            public function findByEmail(Email $email): ?Account
            {
                return $email->toString() === 'customer@example.com' ? $this->account : null;
            }
            public function findByUserId(UserId $userId): ?Account
            {
                return $this->account;
            }
            public function findUserIdByToken(string $token, DateTimeImmutable $now): ?UserId
            {
                return null;
            }
            public function save(Account $account): void {}
            public function replaceToken(UserId $userId, string $token, DateTimeImmutable $expiresAt): void {}
        };
        $issuer = new class implements TokenIssuer {
            public function issue(UserId $userId, Email $email): Token
            {
                return new Token('token-123', new DateTimeImmutable('2026-10-01 UTC'));
            }
        };
        $controller = new IdentityController(
            new LoginHandler($repository, new NativePasswordHasher(), $issuer),
            new RegisterAccountHandler($repository, new NativePasswordHasher()),
        );

        $response = $controller->login(
            (new ServerRequest('POST', '/login'))->withParsedBody(['user_email' => 'customer@example.com', 'user_password' => 'secret']),
            new Response(),
        );
        $payload = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('token-123', $payload['data']['token']);
        self::assertSame('application/json', $response->getHeaderLine('Content-Type'));
    }
}
