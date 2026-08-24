<?php

declare(strict_types=1);

namespace App\DTO;

final class UpdateUserDTO extends AbstractDTO
{
    public static function fromArray(array $data): self
    {
        return new self(CreateUserDTO::validate($data, false));
    }

    public function name(): string { return $this->data['name']; }
    public function email(): string { return $this->data['email']; }
    public function password(): string { return $this->data['password']; }
    public function role(): string { return $this->data['role']; }
}
