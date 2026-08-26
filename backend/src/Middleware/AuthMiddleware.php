<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Service\AuthenticationService;
use App\Support\HttpException;
use App\Support\Request;

final class AuthMiddleware
{
    public function __construct(private readonly AuthenticationService $authentication)
    {
    }

    /** @return array{token: string, user: array<string, mixed>} */
    public function authenticate(Request $request): array
    {
        $token = $request->bearerToken();

        if ($token === null) {
            throw new HttpException(401, 'Token de acesso ausente ou inválido.');
        }

        return ['token' => $token, 'user' => $this->authentication->authenticatedUser($token)];
    }
}
