<?php

declare(strict_types=1);

namespace App\Modules\Identity\Presentation\Http\Middleware;

use App\Modules\Identity\Application\Security\TokenVerifier;
use App\Shared\Application\Error\ErrorResponse;
use App\Shared\Domain\Exception\UnauthorizedException;
use App\Shared\Presentation\Http\Response\JsonResponder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final readonly class AuthenticationMiddleware implements MiddlewareInterface
{
    public function __construct(
        private TokenVerifier $tokens,
        private JsonResponder $responder,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $authorization = trim($request->getHeaderLine('Authorization'));
        $token = str_starts_with(strtolower($authorization), 'bearer ')
            ? trim(substr($authorization, 7))
            : $authorization;
        $userId = $token === '' ? null : $this->tokens->verify($token);

        if ($userId === null) {
            $requestId = $request->getAttribute('request_id');
            return $this->responder->error(ErrorResponse::fromException(
                new UnauthorizedException('Authentication is required.'),
                is_string($requestId) ? $requestId : 'unknown',
            ));
        }

        return $handler->handle($request->withAttribute('authenticated_user_id', $userId));
    }
}
