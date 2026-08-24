<?php

declare(strict_types=1);

namespace App\Support;

final class Router
{
    /** @var array<string, callable(Request): JsonResponse> */
    private array $routes = [];

    /**
     * @param callable(Request): JsonResponse $handler
     */
    public function get(string $path, callable $handler): void
    {
        $this->routes['GET ' . $path] = $handler;
    }

    /**
     * @param callable(Request): JsonResponse $handler
     */
    public function post(string $path, callable $handler): void
    {
        $this->routes['POST ' . $path] = $handler;
    }

    public function dispatch(Request $request): JsonResponse
    {
        $handler = $this->routes[$request->method() . ' ' . $request->path()] ?? null;

        if ($handler === null) {
            return JsonResponse::error('Rota não encontrada.', 404);
        }

        return $handler($request);
    }
}
