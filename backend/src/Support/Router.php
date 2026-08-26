<?php

declare(strict_types=1);

namespace App\Support;

final class Router
{
    /** @var array<string, callable(Request, array<string, string>): JsonResponse> */
    private array $routes = [];

    /**
     * @param callable(Request, array<string, string>): JsonResponse $handler
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

    /** @param callable(Request, array<string, string>): JsonResponse $handler */
    public function put(string $path, callable $handler): void
    {
        $this->routes['PUT ' . $path] = $handler;
    }

    /** @param callable(Request, array<string, string>): JsonResponse $handler */
    public function delete(string $path, callable $handler): void
    {
        $this->routes['DELETE ' . $path] = $handler;
    }

    public function dispatch(Request $request): JsonResponse
    {
        foreach ($this->routes as $route => $handler) {
            [$method, $pattern] = explode(' ', $route, 2);
            if ($method !== $request->method()) {
                continue;
            }
            $expression = preg_replace('/\\{([a-zA-Z_][a-zA-Z0-9_]*)\\}/', '(?P<$1>[^/]+)', $pattern);
            if ($expression !== null && preg_match('#^' . $expression . '$#', $request->path(), $matches) === 1) {
                $parameters = array_filter($matches, static fn (string|int $key): bool => is_string($key), ARRAY_FILTER_USE_KEY);
                return $handler($request, $parameters);
            }
        }
        return JsonResponse::error('Rota não encontrada.', 404);
    }
}
