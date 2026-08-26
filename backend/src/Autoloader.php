<?php

declare(strict_types=1);

namespace App;

final class Autoloader
{
    public static function register(string $sourceDirectory): void
    {
        spl_autoload_register(static function (string $class) use ($sourceDirectory): void {
            $prefix = 'App\\';

            if (!str_starts_with($class, $prefix)) {
                return;
            }

            $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, strlen($prefix)));
            $file = $sourceDirectory . DIRECTORY_SEPARATOR . $relativePath . '.php';

            if (is_file($file)) {
                require $file;
            }
        });
    }
}
