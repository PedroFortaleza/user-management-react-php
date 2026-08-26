<?php

declare(strict_types=1);

namespace App\Repository;

final class UserRepository extends AbstractRepository
{
    private JsonFileRepository $storage;

    public function __construct(string $dataDirectory)
    {
        parent::__construct($dataDirectory);
        $this->storage = new JsonFileRepository($dataDirectory . '/users.json');
    }

    /** @return array<string, mixed>|null */
    public function findByEmail(string $email): ?array
    {
        foreach ($this->storage->read() as $user) {
            if (strtolower((string) ($user['email'] ?? '')) === strtolower($email)) {
                return $user;
            }
        }

        return null;
    }

    /** @return array<string, mixed>|null */
    public function findById(int $id): ?array
    {
        foreach ($this->storage->read() as $user) {
            if ((int) ($user['id'] ?? 0) === $id) {
                return $user;
            }
        }

        return null;
    }

    /** @return list<array<string, mixed>> */
    public function all(): array
    {
        return $this->storage->read();
    }

    /** @param array<string, mixed> $user */
    public function create(array $user): void
    {
        $this->storage->mutate(function (array $users) use ($user): array {
            $highestId = array_reduce($users, static fn (int $highest, array $record): int => max($highest, (int) ($record['id'] ?? 0)), 0);
            $user['id'] = $highestId + 1;
            $users[] = $user;
            return $users;
        });
    }

    /** @param array<string, mixed> $user */
    public function update(int $id, array $user): void
    {
        $this->storage->mutate(function (array $users) use ($id, $user): array {
            foreach ($users as $index => $record) {
                if ((int) ($record['id'] ?? 0) === $id) {
                    $users[$index] = $user;
                    break;
                }
            }
            return $users;
        });
    }

    public function delete(int $id): void
    {
        $this->storage->mutate(static fn (array $users): array => array_values(array_filter($users, static fn (array $user): bool => (int) ($user['id'] ?? 0) !== $id)));
    }

    public function createInitialAdmin(string $passwordHash): void
    {
        $this->storage->mutate(function (array $users) use ($passwordHash): array {
            foreach ($users as $index => $user) {
                if (strtolower((string) ($user['email'] ?? '')) === 'admin@empresa.com') {
                    if (($user['role'] ?? null) !== 'admin') {
                        $users[$index]['role'] = 'admin';
                        $users[$index]['updated_at'] = (new \DateTimeImmutable('now'))->format(DATE_ATOM);
                    }
                    return $users;
                }
            }

            $now = (new \DateTimeImmutable('now'))->format(DATE_ATOM);
            $users[] = [
                'id' => 1,
                'name' => 'Administrador',
                'email' => 'admin@empresa.com',
                'password_hash' => $passwordHash,
                'role' => 'admin',
                'created_at' => $now,
                'updated_at' => $now,
            ];

            return $users;
        });
    }
}
