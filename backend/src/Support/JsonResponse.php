<?php

declare(strict_types=1);

namespace App\Support;

final class JsonResponse
{
    /**
     * @param array<string, mixed> $body
     */
    private function __construct(
        private readonly array $body,
        private readonly int $status = 200,
    ) {
    }

    /**
     * @param array<string, mixed> $body
     */
    public static function success(array $body, int $status = 200): self
    {
        return new self($body, $status);
    }

    public static function error(string $message, int $status): self
    {
        return new self(['message' => $message], $status);
    }

    public function send(): never
    {
        http_response_code($this->status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($this->body, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        exit;
    }
}
