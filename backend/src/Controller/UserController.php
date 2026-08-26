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
    public function index(array $actor): JsonResponse { return JsonResponse::success(['users' => $this->users->list($actor)]); }
    public function show(int $id, array $actor): JsonResponse { return JsonResponse::success(['user' => $this->users->find($id, $actor)]); }
    public function store(Request $request, array $actor): JsonResponse { return JsonResponse::success($this->users->create(CreateUserDTO::fromArray($request->json()), $actor), 201); }
    public function update(Request $request, int $id, array $actor): JsonResponse { return JsonResponse::success($this->users->update($id, UpdateUserDTO::fromArray($request->json()), $actor)); }
    public function destroy(int $id, array $actor): JsonResponse { $this->users->delete($id, $actor); return JsonResponse::success(['message' => 'Usuário removido com sucesso.']); }
    /** @param array<string, string> $parameters */
    public function idFrom(array $parameters): int
    {
        $id = filter_var($parameters['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false) { throw new HttpException(404, 'Usuário não encontrado.'); }
        return $id;
    }
}
