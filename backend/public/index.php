<?php

declare(strict_types=1);

use App\Controller\HealthController;
use App\Controller\AuthController;
use App\Middleware\AuthMiddleware;
use App\Middleware\CorsMiddleware;
use App\Repository\SessionRepository;
use App\Repository\UserRepository;
use App\Service\AuthenticationService;
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
    $authentication = new AuthenticationService(
        new UserRepository(dirname(__DIR__) . '/data'),
        new SessionRepository(dirname(__DIR__) . '/data'),
    );
    $authController = new AuthController($authentication);
    $authMiddleware = new AuthMiddleware($authentication);
    $userController = new \App\Controller\UserController(new \App\Service\UserService(new \App\Repository\UserRepository(dirname(__DIR__) . '/data')));

    $router->get('/api/health', static fn () => $healthController->show());
    $router->post('/api/login', $authController->login(...));
    $router->post('/api/logout', static fn (Request $request) => $authController->logout($authMiddleware->authenticate($request)));
    $router->get('/api/me', static fn (Request $request) => $authController->me($authMiddleware->authenticate($request)));

    $router->get('/api/users', static function (Request $request) use ($authMiddleware, $userController) { return $userController->index($authMiddleware->authenticate($request)['user']); });
    $router->get('/api/users/{id}', static function (Request $request, array $params) use ($authMiddleware, $userController) { return $userController->show($userController->idFrom($params), $authMiddleware->authenticate($request)['user']); });
    $router->post('/api/users', static function (Request $request) use ($authMiddleware, $userController) { return $userController->store($request, $authMiddleware->authenticate($request)['user']); });
    $router->put('/api/users/{id}', static function (Request $request, array $params) use ($authMiddleware, $userController) { return $userController->update($request, $userController->idFrom($params), $authMiddleware->authenticate($request)['user']); });
    $router->delete('/api/users/{id}', static fn (Request $request, array $params) => $userController->destroy($userController->idFrom($params), $authMiddleware->authenticate($request)['user']));

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
