<?php

declare(strict_types=1);

namespace App\Support;

final class Request
{
    private function __construct(
        private readonly string $method,
        private readonly string $path,
    ) {
    }

    public static function fromGlobals(): self
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH);

        return new self(
            strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'),
            is_string($path) ? rtrim($path, '/') ?: '/' : '/',
        );
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }
}
