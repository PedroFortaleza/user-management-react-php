<?php

declare(strict_types=1);

namespace App\Support;

final class Validator
{
    /**
     * @param array<string, mixed> $data
     * @param list<string> $fields
     */
    public static function required(array $data, array $fields): void
    {
        $errors = [];

        foreach ($fields as $field) {
            if (!isset($data[$field]) || trim((string) $data[$field]) === '') {
                $errors[$field][] = 'Este campo é obrigatório.';
            }
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }
    }

    public static function email(string $value): void
    {
        if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            throw new ValidationException(['email' => ['Informe um e-mail válido.']]);
        }
    }
}
