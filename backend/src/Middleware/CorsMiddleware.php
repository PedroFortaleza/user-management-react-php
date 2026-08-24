<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Support\Request;

final class CorsMiddleware
{
    public function apply(Request $request): bool
    {
        header('Access-Control-Allow-Origin: ' . ($_ENV['CORS_ORIGIN'] ?? 'http://localhost:5173'));
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Vary: Origin');

        return $request->method() === 'OPTIONS';
    }
}
