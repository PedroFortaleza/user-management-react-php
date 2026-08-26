<?php

declare(strict_types=1);

namespace App\Support;

final class Request
{
    private function __construct(
        private readonly string $method,
        private readonly string $path,
        /** @var array<string, string> */
        private readonly array $headers,
    ) {
    }

    public static function fromGlobals(): self
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH);

        return new self(
            strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'),
            is_string($path) ? rtrim($path, '/') ?: '/' : '/',
            self::headersFromGlobals(),
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

    /** @return array<string, mixed> */
    public function json(): array
    {
        $body = file_get_contents('php://input');

        if ($body === false || trim($body) === '') {
            throw new HttpException(400, 'O corpo da requisição deve conter JSON válido.');
        }

        try {
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new HttpException(400, 'O corpo da requisição deve conter JSON válido.');
        }

        if (!is_array($data)) {
            throw new HttpException(400, 'O corpo da requisição deve ser um objeto JSON.');
        }

        return $data;
    }

    public function bearerToken(): ?string
    {
        $authorization = $this->headers['authorization'] ?? '';

        if (preg_match('/^Bearer\\s+(.+)$/i', $authorization, $matches) !== 1) {
            return null;
        }

        return trim($matches[1]) ?: null;
    }

    /** @return array<string, string> */
    private static function headersFromGlobals(): array
    {
        $headers = [];

        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $headers[strtolower(str_replace('_', '-', substr($key, 5)))] = (string) $value;
            }
        }

        return $headers;
    }
}
