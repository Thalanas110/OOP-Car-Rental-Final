<?php

declare(strict_types=1);

namespace Tests\Http;

use App\Modules\Identity\Application\Command\ChangePasswordHandler;
use App\Modules\Identity\Application\Command\LoginHandler;
use App\Modules\Identity\Application\Command\RegisterAccountHandler;
use App\Modules\Identity\Application\Port\PasswordUpdater;
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

final class IdentityPasswordControllerTest extends TestCase
{
    public function testPasswordPatchUsesAuthenticatedUserIdAndReturnsSuccess(): void
    {
        $updater = new class implements PasswordUpdater {
            public ?UserId $userId = null;
            public function update(UserId $userId, \App\Modules\Identity\Domain\ValueObject\PasswordHash $password): void { $this->userId = $userId; }
        };
        $repository = new class implements AccountRepository {
            public function findByEmail(Email $email): ?Account { return null; }
            public function findByUserId(UserId $userId): ?Account { return null; }
            public function findUserIdByToken(string $token, DateTimeImmutable $now): ?UserId { return null; }
            public function save(Account $account): void {}
            public function replaceToken(UserId $userId, string $token, DateTimeImmutable $expiresAt): void {}
        };
        $issuer = new class implements TokenIssuer {
            public function issue(UserId $userId, Email $email): Token { return new Token('unused', new DateTimeImmutable('2030-01-01 UTC')); }
        };
        $controller = new IdentityController(
            new LoginHandler($repository, new NativePasswordHasher(), $issuer),
            new RegisterAccountHandler($repository, new NativePasswordHasher()),
            new ChangePasswordHandler($updater, new NativePasswordHasher()),
        );

        $response = $controller->changePassword(
            (new ServerRequest('PATCH', '/useraccount'))
                ->withParsedBody(['user_password' => 'new-secret'])
                ->withAttribute('authenticated_user_id', UserId::fromInt(7)),
            new Response(),
        );
        $payload = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(7, $updater->userId?->toInt());
        self::assertSame('Password updated successfully.', $payload['data']['message']);
    }
}
