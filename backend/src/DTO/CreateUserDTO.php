<?php

declare(strict_types=1);

namespace App\DTO;

use App\Support\ValidationException;

final class CreateUserDTO extends AbstractDTO
{
    public static function fromArray(array $data): self
    {
        return new self(self::validate($data, true));
    }

    /** @return array<string, string> */
    public static function validate(array $data, bool $passwordRequired): array
    {
        $values = [
            'name' => trim((string) ($data['name'] ?? '')),
            'email' => strtolower(trim((string) ($data['email'] ?? ''))),
            'password' => (string) ($data['password'] ?? ''),
            'role' => trim((string) ($data['role'] ?? '')),
        ];
        $errors = [];
        if ($values['name'] === '') { $errors['name'][] = 'O nome é obrigatório.'; }
        if ($values['email'] === '') { $errors['email'][] = 'O e-mail é obrigatório.'; }
        elseif (filter_var($values['email'], FILTER_VALIDATE_EMAIL) === false) { $errors['email'][] = 'Informe um e-mail válido.'; }
        if ($passwordRequired && $values['password'] === '') { $errors['password'][] = 'A senha é obrigatória.'; }
        elseif ($values['password'] !== '' && strlen($values['password']) < 8) { $errors['password'][] = 'A senha deve ter ao menos 8 caracteres.'; }
        if ($values['role'] === '') { $errors['role'][] = 'O perfil é obrigatório.'; }
        elseif (!in_array($values['role'], ['admin', 'user'], true)) { $errors['role'][] = 'Escolha um perfil válido.'; }
        if ($errors !== []) { throw new ValidationException($errors); }
        return $values;
    }

    public function name(): string { return $this->data['name']; }
    public function email(): string { return $this->data['email']; }
    public function password(): string { return $this->data['password']; }
    public function role(): string { return $this->data['role']; }
}
