<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Http\Middleware;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class RequestIdMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requestId = $request->getHeaderLine('X-Request-Id');
        if ($requestId === '') {
            $requestId = $this->generateRequestId();
        }

        $response = $handler->handle($request->withAttribute('request_id', $requestId));

        return $response instanceof Response ? $response->withHeader('X-Request-Id', $requestId) : $response->withHeader('X-Request-Id', $requestId);
    }

    private function generateRequestId(): string
    {
        $bytes = bin2hex(random_bytes(16));

        return sprintf('%s-%s-%s-%s-%s', substr($bytes, 0, 8), substr($bytes, 8, 4), substr($bytes, 12, 4), substr($bytes, 16, 4), substr($bytes, 20, 12));
    }
}
