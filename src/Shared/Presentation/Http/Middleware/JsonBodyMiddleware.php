<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Http\Middleware;

use JsonException;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class JsonBodyMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $body = (string) $request->getBody();
        if ($body === '' || !str_contains(strtolower($request->getHeaderLine('Content-Type')), 'application/json')) {
            return $handler->handle($request);
        }

        try {
            $parsedBody = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return new Response(
                400,
                ['Content-Type' => 'application/json'],
                json_encode(['error' => ['code' => 'invalid_json', 'message' => 'Request body must contain valid JSON.']], JSON_THROW_ON_ERROR),
            );
        }

        if (!is_array($parsedBody)) {
            return new Response(
                400,
                ['Content-Type' => 'application/json'],
                json_encode(['error' => ['code' => 'invalid_json', 'message' => 'Request body must contain a JSON object.']], JSON_THROW_ON_ERROR),
            );
        }

        return $handler->handle($request->withParsedBody($parsedBody));
    }
}
