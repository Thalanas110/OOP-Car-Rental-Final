<?php

declare(strict_types=1);

namespace Tests\Http;

use App\Shared\Application\Auth\AuthorizationPolicy;
use App\Shared\Presentation\Http\Middleware\AuthorizationMiddleware;
use App\Shared\Presentation\Http\Response\JsonResponder;
use App\Shared\Domain\ValueObject\UserId;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class AuthorizationMiddlewareTest extends TestCase
{
    public function testRejectsUnauthenticatedProtectedRequest(): void
    {
        $response = (new AuthorizationMiddleware(new AuthorizationPolicy(), new JsonResponder()))->process(new ServerRequest('POST', '/cars'), new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface { return new Response(200); }
        });

        self::assertSame(401, $response->getStatusCode());
    }

    public function testAllowsAdminOnlyRequestForConfiguredAdmin(): void
    {
        $request = (new ServerRequest('DELETE', '/destroycars/1'))->withAttribute('authenticated_user_id', UserId::fromInt(1));
        $response = (new AuthorizationMiddleware(new AuthorizationPolicy(adminUserId: 1), new JsonResponder(), adminOnly: true))->process($request, new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface { return new Response(200); }
        });

        self::assertSame(200, $response->getStatusCode());
    }

    public function testRejectsNonAdminDestroyRequest(): void
    {
        $request = (new ServerRequest('DELETE', '/destroycars/1'))->withAttribute('authenticated_user_id', UserId::fromInt(7));
        $response = (new AuthorizationMiddleware(new AuthorizationPolicy(adminUserId: 1), new JsonResponder(), adminOnly: true))->process($request, new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface { return new Response(200); }
        });

        self::assertSame(403, $response->getStatusCode());
    }
}
