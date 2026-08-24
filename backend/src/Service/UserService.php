<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\CreateUserDTO;
use App\DTO\UpdateUserDTO;
use App\Repository\UserRepository;
use App\Support\HttpException;
use App\Support\ValidationException;

final class UserService
{
    public function __construct(private readonly UserRepository $users) {}

    /** @return list<array<string, mixed>> */
    public function list(array $actor): array
    {
        if ($actor['role'] === 'admin') { return array_map($this->safeUser(...), $this->users->all()); }
        return [$this->find((int) $actor['id'], $actor)];
    }

    /** @return array<string, mixed> */
    public function find(int $id, array $actor): array
    {
        $this->requireAdminOrSelf($actor, $id);
        return $this->safeUser($this->requireUser($id));
    }

    /** @return array<string, mixed> */
    public function create(CreateUserDTO $input, array $actor): array
    {
        $this->requireAdmin($actor);
        $this->ensureEmailAvailable($input->email());
        $now = (new \DateTimeImmutable('now'))->format(DATE_ATOM);
        $this->users->create(['name' => $input->name(), 'email' => $input->email(), 'password_hash' => password_hash($input->password(), PASSWORD_DEFAULT), 'role' => $input->role(), 'created_at' => $now, 'updated_at' => $now]);
        return $this->safeUser($this->users->findByEmail($input->email()) ?? throw new \RuntimeException('Falha ao criar usuário.'));
    }

    /** @return array<string, mixed> */
    public function update(int $id, UpdateUserDTO $input, array $actor): array
    {
        $this->requireAdmin($actor);
        $user = $this->requireUser($id);
        $this->ensureEmailAvailable($input->email(), $id);
        $user['name'] = $input->name();
        $user['email'] = $input->email();
        $user['role'] = $input->role();
        $user['updated_at'] = (new \DateTimeImmutable('now'))->format(DATE_ATOM);
        if ($input->password() !== '') { $user['password_hash'] = password_hash($input->password(), PASSWORD_DEFAULT); }
        $this->users->update($id, $user);
        return $this->safeUser($user);
    }

    public function delete(int $id, array $actor): void
    {
        $this->requireAdmin($actor);
        $this->requireUser($id);
        if ($id === (int) $actor['id']) { throw new ValidationException(['user' => ['Você não pode excluir a própria conta.']]); }
        $this->users->delete($id);
    }

    private function ensureEmailAvailable(string $email, ?int $exceptId = null): void
    {
        $existing = $this->users->findByEmail($email);
        if ($existing !== null && (int) $existing['id'] !== $exceptId) { throw new ValidationException(['email' => ['Este e-mail já está em uso.']]); }
    }

    /** @param array<string, mixed> $actor */
    private function requireAdmin(array $actor): void { if (($actor['role'] ?? null) !== 'admin') { throw new HttpException(403, 'Você não tem permissão para esta ação.'); } }
    /** @param array<string, mixed> $actor */
    private function requireAdminOrSelf(array $actor, int $id): void { if (($actor['role'] ?? null) !== 'admin' && (int) ($actor['id'] ?? 0) !== $id) { throw new HttpException(403, 'Você não tem permissão para consultar este usuário.'); } }

    /** @return array<string, mixed> */
    private function requireUser(int $id): array { return $this->users->findById($id) ?? throw new HttpException(404, 'Usuário não encontrado.'); }

    /** @param array<string, mixed> $user
     * @return array<string, mixed>
     */
    private function safeUser(array $user): array
    {
        return ['id' => (int) $user['id'], 'name' => (string) $user['name'], 'email' => (string) $user['email'], 'role' => (string) $user['role'], 'created_at' => (string) $user['created_at'], 'updated_at' => (string) $user['updated_at']];
    }
}
