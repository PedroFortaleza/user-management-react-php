<?php

declare(strict_types=1);

namespace App\Repository;

final class SessionRepository extends AbstractRepository
{
    private JsonFileRepository $storage;

    public function __construct(string $dataDirectory)
    {
        parent::__construct($dataDirectory);
        $this->storage = new JsonFileRepository($dataDirectory . '/sessions.json');
    }

    public function create(int $userId, string $tokenHash, \DateTimeImmutable $expiresAt): void
    {
        $this->storage->mutate(function (array $sessions) use ($userId, $tokenHash, $expiresAt): array {
            $now = new \DateTimeImmutable('now');
            $sessions = array_values(array_filter($sessions, static fn (array $session): bool => new \DateTimeImmutable((string) $session['expires_at']) > $now));
            $sessions[] = [
                'user_id' => $userId,
                'token_hash' => $tokenHash,
                'expires_at' => $expiresAt->format(DATE_ATOM),
                'created_at' => $now->format(DATE_ATOM),
            ];

            return $sessions;
        });
    }

    /** @return array<string, mixed>|null */
    public function findActiveByToken(string $token): ?array
    {
        $hash = hash('sha256', $token);
        $now = new \DateTimeImmutable('now');

        foreach ($this->storage->read() as $session) {
            if (hash_equals((string) ($session['token_hash'] ?? ''), $hash)
                && new \DateTimeImmutable((string) $session['expires_at']) > $now) {
                return $session;
            }
        }

        return null;
    }

    public function deleteByToken(string $token): void
    {
        $hash = hash('sha256', $token);
        $this->storage->mutate(static fn (array $sessions): array => array_values(array_filter(
            $sessions,
            static fn (array $session): bool => !hash_equals((string) ($session['token_hash'] ?? ''), $hash),
        )));
    }
}
