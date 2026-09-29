<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

final readonly class DocsController
{
    public function __construct(private string $documentPath, private string $uiPath) {}

    public function ui(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->serve($response, $this->uiPath, 'text/html; charset=utf-8');
    }

    public function document(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->serve($response, $this->documentPath, 'application/yaml; charset=utf-8');
    }

    private function serve(ResponseInterface $response, string $path, string $contentType): ResponseInterface
    {
        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new RuntimeException('Documentation asset is unavailable.');
        }

        $response->getBody()->write($contents);
        return $response->withHeader('Content-Type', $contentType);
    }
}
