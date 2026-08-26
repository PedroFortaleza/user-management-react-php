<?php

declare(strict_types=1);

namespace App\Repository;

abstract class AbstractRepository
{
    public function __construct(protected readonly string $dataDirectory)
    {
    }
}
