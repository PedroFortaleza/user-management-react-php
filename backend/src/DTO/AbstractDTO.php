<?php

declare(strict_types=1);

namespace App\DTO;

abstract class AbstractDTO
{
    /**
     * @param array<string, mixed> $data
     */
    final public function __construct(protected readonly array $data)
    {
    }
}
