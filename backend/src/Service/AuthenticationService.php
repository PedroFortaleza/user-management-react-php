<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\LoginDTO;
use App\Repository\SessionRepository;
use App\Repository\UserRepository;
use App\Support\HttpException;

final class AuthenticationService
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly SessionRepository $sessions,
    ) {
    }

    /** @return array{token: string, user: array<string, mixed>} */
    public function login(LoginDTO $credentials): array
    {
        $user = $this->users->findByEmail($credentials->email());

        if ($user === null || !password_verify($credentials->password(), (string) ($user['password_hash'] ?? ''))) {
            throw new HttpException(401, 'E-mail ou senha inválidos.');
        }

        $token = bin2hex(random_bytes(32));
        $ttl = max(1, (int) ($_ENV['TOKEN_TTL_MINUTES'] ?? 480));
        $expiresAt = (new \DateTimeImmutable('now'))->modify("+{$ttl} minutes");
        $this->sessions->create((int) $user['id'], hash('sha256', $token), $expiresAt);

        return ['token' => $token, 'user' => $this->safeUser($user)];
    }

    /** @return array<string, mixed> */
    public function authenticatedUser(string $token): array
    {
        $session = $this->sessions->findActiveByToken($token);

        if ($session === null) {
            throw new HttpException(401, 'Sessão inválida ou expirada.');
        }

        $user = $this->users->findById((int) $session['user_id']);

        if ($user === null) {
            $this->sessions->deleteByToken($token);
            throw new HttpException(401, 'Sessão inválida ou expirada.');
        }

        return $this->safeUser($user);
    }

    public function logout(string $token): void
    {
        $this->sessions->deleteByToken($token);
    }

    /** @param array<string, mixed> $user
     * @return array<string, mixed>
     */
    private function safeUser(array $user): array
    {
        return [
            'id' => (int) $user['id'],
            'name' => (string) $user['name'],
            'email' => (string) $user['email'],
            'role' => (string) $user['role'],
            'created_at' => (string) $user['created_at'],
            'updated_at' => (string) $user['updated_at'],
        ];
    }
}
