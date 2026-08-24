<?php

declare(strict_types=1);

use App\Repository\UserRepository;

require __DIR__ . '/src/bootstrap.php';

$repository = new UserRepository(__DIR__ . '/data');
$repository->createInitialAdmin(password_hash('Admin@123', PASSWORD_DEFAULT));

echo "Administrador disponível: admin@empresa.com / Admin@123" . PHP_EOL;
