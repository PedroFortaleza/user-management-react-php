<?php

declare(strict_types=1);

use App\Controller\HealthController;
use App\Middleware\CorsMiddleware;
use App\Service\HealthService;
use App\Support\HttpException;
use App\Support\JsonResponse;
use App\Support\Request;
use App\Support\Router;
use App\Support\ValidationException;

require dirname(__DIR__) . '/src/bootstrap.php';

$request = Request::fromGlobals();

try {
    if ((new CorsMiddleware())->apply($request)) {
        JsonResponse::success([], 204)->send();
    }

    $router = new Router();
    $healthController = new HealthController(new HealthService());

    $router->get('/api/health', $healthController->show(...));

    $router->dispatch($request)->send();
} catch (ValidationException $exception) {
    JsonResponse::success([
        'message' => $exception->getMessage(),
        'errors' => $exception->errors(),
    ], $exception->status())->send();
} catch (HttpException $exception) {
    JsonResponse::error($exception->getMessage(), $exception->status())->send();
} catch (Throwable) {
    JsonResponse::error('Erro interno do servidor.', 500)->send();
}
