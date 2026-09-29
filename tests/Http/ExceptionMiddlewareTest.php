<?php

declare(strict_types=1);

namespace Tests\Http;

use App\Shared\Domain\Exception\ConflictException;
use App\Shared\Presentation\Http\Middleware\ExceptionMiddleware;
use App\Shared\Presentation\Http\Response\JsonResponder;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ExceptionMiddlewareTest extends TestCase
{
    public function testMapsDomainExceptionToSafeJson(): void
    {
        $request = (new ServerRequest('GET', '/cars'))->withAttribute('request_id', 'req-1');
        $response = (new ExceptionMiddleware(new JsonResponder()))->process($request, new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface { throw new ConflictException('Overlap.'); }
        });
        $payload = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(409, $response->getStatusCode());
        self::assertSame('conflict', $payload['error']['code']);
        self::assertSame('req-1', $payload['meta']['request_id']);
    }

    public function testHidesUnexpectedExceptionMessage(): void
    {
        $response = (new ExceptionMiddleware(new JsonResponder()))->process(new ServerRequest('GET', '/cars'), new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface { throw new \RuntimeException('database password'); }
        });
        $payload = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(500, $response->getStatusCode());
        self::assertSame('An unexpected error occurred.', $payload['error']['message']);
    }
}
