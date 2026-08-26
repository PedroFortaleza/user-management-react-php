<?php

declare(strict_types=1);

namespace App\Support;

final class ValidationException extends HttpException
{
    /**
     * @param array<string, list<string>> $errors
     */
    public function __construct(private readonly array $errors)
    {
        parent::__construct(422, 'Falha de validação.');
    }

    /**
     * @return array<string, list<string>>
     */
    public function errors(): array
    {
        return $this->errors;
    }
}
