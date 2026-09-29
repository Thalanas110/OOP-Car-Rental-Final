<?php

declare(strict_types=1);

namespace Tests\Http;

use App\Modules\Identity\Application\Security\TokenVerifier;
use App\Modules\Identity\Presentation\Http\Middleware\AuthenticationMiddleware;
use App\Shared\Domain\ValueObject\UserId;
use App\Shared\Presentation\Http\Response\JsonResponder;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class IdentityAuthenticationMiddlewareTest extends TestCase
{
    public function testRejectsMissingCredentials(): void
    {
        $verifier = new class implements TokenVerifier {
            public function verify(string $token): ?UserId { return null; }
        };

        $response = (new AuthenticationMiddleware($verifier, new JsonResponder()))->process(
            new ServerRequest('GET', '/cars'),
            new class implements RequestHandlerInterface {
                public function handle(ServerRequestInterface $request): ResponseInterface { return new Response(200); }
            },
        );

        self::assertSame(401, $response->getStatusCode());
    }

    public function testAcceptsBearerTokenAndAddsAuthenticatedUserId(): void
    {
        $captured = null;
        $verifier = new class implements TokenVerifier {
            public function verify(string $token): ?UserId
            {
                return $token === 'token-123' ? UserId::fromInt(7) : null;
            }
        };
        $handler = new class ($captured) implements RequestHandlerInterface {
            public function __construct(private mixed &$captured) {}
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $this->captured = $request->getAttribute('authenticated_user_id');
                return new Response(200);
            }
        };

        $response = (new AuthenticationMiddleware($verifier, new JsonResponder()))->process(
            (new ServerRequest('GET', '/cars'))->withHeader('Authorization', 'Bearer token-123'),
            $handler,
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertInstanceOf(UserId::class, $captured);
        self::assertSame(7, $captured->toInt());
    }

    public function testAcceptsLegacyRawAuthorizationToken(): void
    {
        $verifier = new class implements TokenVerifier {
            public function verify(string $token): ?UserId
            {
                return $token === 'legacy-token' ? UserId::fromInt(8) : null;
            }
        };

        $response = (new AuthenticationMiddleware($verifier, new JsonResponder()))->process(
            (new ServerRequest('GET', '/cars'))->withHeader('Authorization', 'legacy-token')->withHeader('X-Auth-User', 'customer@example.com'),
            new class implements RequestHandlerInterface {
                public function handle(ServerRequestInterface $request): ResponseInterface { return new Response(200); }
            },
        );

        self::assertSame(200, $response->getStatusCode());
    }
}
