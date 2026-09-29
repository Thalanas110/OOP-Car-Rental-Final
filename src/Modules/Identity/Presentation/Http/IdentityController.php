<?php

declare(strict_types=1);

namespace App\Modules\Identity\Presentation\Http;

use App\Modules\Identity\Application\Command\Login;
use App\Modules\Identity\Application\Command\LoginHandler;
use App\Modules\Identity\Application\Command\ChangePassword;
use App\Modules\Identity\Application\Command\ChangePasswordHandler;
use App\Modules\Identity\Application\Command\RegisterAccount;
use App\Modules\Identity\Application\Command\RegisterAccountHandler;
use App\Shared\Domain\Exception\ValidationException;
use App\Shared\Domain\ValueObject\UserId;
use App\Shared\Presentation\Http\Response\JsonResponder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class IdentityController
{
    private JsonResponder $responder;

    public function __construct(
        private LoginHandler $loginHandler,
        private RegisterAccountHandler $registerAccountHandler,
        private ?ChangePasswordHandler $changePasswordHandler = null,
        ?JsonResponder $responder = null,
    ) {
        $this->responder = $responder ?? new JsonResponder();
    }

    public function login(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body = $this->body($request);
        $view = ($this->loginHandler)(new Login(
            $this->requiredString($body, 'user_email'),
            $this->requiredString($body, 'user_password'),
        ));

        return $this->responder->success($view->toArray(), 200, $this->meta($request));
    }

    public function register(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body = $this->body($request);
        $view = ($this->registerAccountHandler)(new RegisterAccount(
            $this->requiredInt($body, 'userID'),
            $this->requiredString($body, 'user_email'),
            $this->requiredString($body, 'user_password'),
        ));

        return $this->responder->success($view->toArray(), 201, $this->meta($request));
    }

    public function changePassword(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $userId = $request->getAttribute('authenticated_user_id');
        if (!$userId instanceof UserId || $this->changePasswordHandler === null) {
            throw new ValidationException('Authenticated user is required.');
        }

        $view = ($this->changePasswordHandler)(new ChangePassword($userId, $this->requiredString($this->body($request), 'user_password')));

        return $this->responder->success($view->toArray(), 200, $this->meta($request));
    }

    /** @return array<string, mixed> */
    private function body(ServerRequestInterface $request): array
    {
        $body = $request->getParsedBody();

        if (!is_array($body)) {
            throw new ValidationException('Request body must be a JSON object.');
        }

        return $body;
    }

    /** @param array<string, mixed> $body */
    private function requiredString(array $body, string $key): string
    {
        $value = $body[$key] ?? null;
        if (!is_string($value) || trim($value) === '') {
            throw new ValidationException(sprintf('%s is required.', $key));
        }

        return $value;
    }

    /** @param array<string, mixed> $body */
    private function requiredInt(array $body, string $key): int
    {
        $value = $body[$key] ?? null;
        if (!is_int($value) && !(is_string($value) && ctype_digit($value))) {
            throw new ValidationException(sprintf('%s must be a positive integer.', $key));
        }

        $integer = (int) $value;
        if ($integer < 1) {
            throw new ValidationException(sprintf('%s must be a positive integer.', $key));
        }

        return $integer;
    }

    /** @return array<string, mixed> */
    private function meta(ServerRequestInterface $request): array
    {
        $requestId = $request->getAttribute('request_id');
        return ['request_id' => is_string($requestId) ? $requestId : 'unknown'];
    }
}
