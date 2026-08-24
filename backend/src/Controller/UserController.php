<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\CreateUserDTO;
use App\DTO\UpdateUserDTO;
use App\Service\UserService;
use App\Support\HttpException;
use App\Support\JsonResponse;
use App\Support\Request;

final class UserController
{
    public function __construct(private readonly UserService $users) {}
    public function index(): JsonResponse { return JsonResponse::success(['users' => $this->users->list()]); }
    public function show(int $id): JsonResponse { return JsonResponse::success(['user' => $this->users->find($id)]); }
    public function store(Request $request): JsonResponse { return JsonResponse::success($this->users->create(CreateUserDTO::fromArray($request->json())), 201); }
    public function update(Request $request, int $id): JsonResponse { return JsonResponse::success($this->users->update($id, UpdateUserDTO::fromArray($request->json()))); }
    public function destroy(int $id, int $authenticatedId): JsonResponse { $this->users->delete($id, $authenticatedId); return JsonResponse::success(['message' => 'Usuário removido com sucesso.']); }
    /** @param array<string, string> $parameters */
    public function idFrom(array $parameters): int
    {
        $id = filter_var($parameters['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false) { throw new HttpException(404, 'Usuário não encontrado.'); }
        return $id;
    }
}
