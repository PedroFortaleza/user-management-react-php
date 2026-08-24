<?php

declare(strict_types=1);

namespace App\DTO;

use App\Support\Validator;

final class LoginDTO extends AbstractDTO
{
    public static function fromArray(array $data): self
    {
        Validator::required($data, ['email', 'password']);
        $email = strtolower(trim((string) $data['email']));
        Validator::email($email);

        return new self([
            'email' => $email,
            'password' => (string) $data['password'],
        ]);
    }

    public function email(): string
    {
        return $this->data['email'];
    }

    public function password(): string
    {
        return $this->data['password'];
    }
}
