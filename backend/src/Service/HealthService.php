<?php

declare(strict_types=1);

namespace App\Service;

final class HealthService
{
    /**
     * @return array{status: string}
     */
    public function check(): array
    {
        return ['status' => 'ok'];
    }
}
