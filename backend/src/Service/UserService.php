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
    public function list(): array { return array_map($this->safeUser(...), $this->users->all()); }

    /** @return array<string, mixed> */
    public function find(int $id): array { return $this->safeUser($this->requireUser($id)); }

    /** @return array<string, mixed> */
    public function create(CreateUserDTO $input): array
    {
        $this->ensureEmailAvailable($input->email());
        $now = (new \DateTimeImmutable('now'))->format(DATE_ATOM);
        $this->users->create(['name' => $input->name(), 'email' => $input->email(), 'password_hash' => password_hash($input->password(), PASSWORD_DEFAULT), 'role' => $input->role(), 'created_at' => $now, 'updated_at' => $now]);
        return $this->safeUser($this->users->findByEmail($input->email()) ?? throw new \RuntimeException('Falha ao criar usuário.'));
    }

    /** @return array<string, mixed> */
    public function update(int $id, UpdateUserDTO $input): array
    {
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

    public function delete(int $id, int $authenticatedId): void
    {
        $this->requireUser($id);
        if ($id === $authenticatedId) { throw new ValidationException(['user' => ['Você não pode excluir a própria conta.']]); }
        $this->users->delete($id);
    }

    private function ensureEmailAvailable(string $email, ?int $exceptId = null): void
    {
        $existing = $this->users->findByEmail($email);
        if ($existing !== null && (int) $existing['id'] !== $exceptId) { throw new ValidationException(['email' => ['Este e-mail já está em uso.']]); }
    }

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
