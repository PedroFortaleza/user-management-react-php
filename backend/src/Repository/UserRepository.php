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

    public function createInitialAdmin(string $passwordHash): void
    {
        $this->storage->mutate(function (array $users) use ($passwordHash): array {
            foreach ($users as $user) {
                if (strtolower((string) ($user['email'] ?? '')) === 'admin@empresa.com') {
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
