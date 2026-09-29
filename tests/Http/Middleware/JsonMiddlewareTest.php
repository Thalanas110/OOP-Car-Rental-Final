<?php

declare(strict_types=1);

namespace Tests\Http\Middleware;

use App\Shared\Presentation\Http\Middleware\JsonBodyMiddleware;
use App\Shared\Presentation\Http\Middleware\RequestIdMiddleware;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Stream;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class JsonMiddlewareTest extends TestCase
{
    public function testDecodesJsonAndAddsRequestId(): void
    {
        $captured = null;
        $request = (new ServerRequest('POST', '/cars'))
            ->withHeader('Content-Type', 'application/json')
            ->withBody(Stream::create('{"name":"Ada"}'));
        $handler = new class ($captured) implements RequestHandlerInterface {
            public function __construct(private mixed &$captured) {}

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $this->captured = $request->getParsedBody();

                return new Response(200);
            }
        };

        $response = (new RequestIdMiddleware())->process(
            $request,
            new class ($handler) implements RequestHandlerInterface {
                public function __construct(private RequestHandlerInterface $handler) {}

                public function handle(ServerRequestInterface $request): ResponseInterface
                {
                    return (new JsonBodyMiddleware())->process($request, $this->handler);
                }
            },
        );

        self::assertSame(['name' => 'Ada'], $captured);
        self::assertMatchesRegularExpression('/^[a-f0-9-]{36}$/', $response->getHeaderLine('X-Request-Id'));
    }

    public function testRejectsMalformedJson(): void
    {
        $request = (new ServerRequest('POST', '/cars'))
            ->withHeader('Content-Type', 'application/json')
            ->withBody(Stream::create('{bad'));

        $response = (new JsonBodyMiddleware())->process($request, new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new Response(200);
            }
        });

        self::assertSame(400, $response->getStatusCode());
    }
}
