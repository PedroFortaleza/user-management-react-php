<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\HealthService;
use App\Support\JsonResponse;

final class HealthController
{
    public function __construct(private readonly HealthService $healthService)
    {
    }

    public function show(): JsonResponse
    {
        return JsonResponse::success($this->healthService->check());
    }
}
