<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Http;

use Psr\Http\Message\ServerRequestInterface;

final class LegacyRequestPath
{
    /** @return list<string> */
    public static function fromRequest(ServerRequestInterface $request): array
    {
        $value = $request->getQueryParams()['request'] ?? '';
        if (!is_string($value) || trim($value) === '') { return []; }
        $segments = explode('/', trim($value, '/'));
        return array_values(array_filter(array_map(static fn (string $segment): string => rawurldecode($segment), $segments), static fn (string $segment): bool => $segment !== ''));
    }
}
