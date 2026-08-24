<?php

declare(strict_types=1);

use App\DTO\CreateUserDTO;
use App\Repository\UserRepository;
use App\Service\UserService;
use App\Support\HttpException;

require dirname(__DIR__) . '/src/bootstrap.php';

$directory = sys_get_temp_dir() . '/user-management-' . bin2hex(random_bytes(4));
$service = new UserService(new UserRepository($directory));
$admin = ['id' => 1, 'role' => 'admin'];
$created = $service->create(CreateUserDTO::fromArray(['name' => 'Teste', 'email' => 'teste@example.com', 'password' => 'Senha@123', 'role' => 'user']), $admin);
if ($created['email'] !== 'teste@example.com' || array_key_exists('password_hash', $created)) { throw new RuntimeException('Criação insegura.'); }
try { $service->create(CreateUserDTO::fromArray(['name' => 'Duplicado', 'email' => 'teste@example.com', 'password' => 'Senha@123', 'role' => 'user']), $admin); throw new RuntimeException('Duplicidade permitida.'); } catch (\App\Support\ValidationException) {}
try { $service->list(['id' => $created['id'], 'role' => 'user']); } catch (HttpException $exception) { throw new RuntimeException($exception->getMessage()); }
try { $service->create(CreateUserDTO::fromArray(['name' => 'Negado', 'email' => 'negado@example.com', 'password' => 'Senha@123', 'role' => 'user']), ['id' => $created['id'], 'role' => 'user']); throw new RuntimeException('RBAC falhou.'); } catch (HttpException $exception) { if ($exception->status() !== 403) { throw $exception; } }
echo "Backend tests passed\n";
