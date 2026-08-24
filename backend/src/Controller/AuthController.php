<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\LoginDTO;
use App\Service\AuthenticationService;
use App\Support\JsonResponse;
use App\Support\Request;

final class AuthController
{
    public function __construct(private readonly AuthenticationService $authentication)
    {
    }

    public function login(Request $request): JsonResponse
    {
        return JsonResponse::success($this->authentication->login(LoginDTO::fromArray($request->json())));
    }

    /** @param array{token: string, user: array<string, mixed>} $session */
    public function logout(array $session): JsonResponse
    {
        $this->authentication->logout($session['token']);

        return JsonResponse::success(['message' => 'Sessão encerrada com sucesso.']);
    }

    /** @param array{token: string, user: array<string, mixed>} $session */
    public function me(array $session): JsonResponse
    {
        return JsonResponse::success(['user' => $session['user']]);
    }
}
