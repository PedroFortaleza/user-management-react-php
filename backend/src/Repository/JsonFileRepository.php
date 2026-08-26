<?php

declare(strict_types=1);

namespace App\Repository;

use RuntimeException;

final class JsonFileRepository
{
    public function __construct(private readonly string $path)
    {
    }

    /** @return list<array<string, mixed>> */
    public function read(): array
    {
        return $this->withLock(LOCK_SH, fn (): array => $this->readUnlocked());
    }

    /**
     * @param callable(list<array<string, mixed>>): list<array<string, mixed>> $mutation
     */
    public function mutate(callable $mutation): void
    {
        $this->withLock(LOCK_EX, function () use ($mutation): void {
            $records = $mutation($this->readUnlocked());
            $this->writeUnlocked($records);
        });
    }

    /**
     * @template T
     * @param callable(): T $operation
     * @return T
     */
    private function withLock(int $lockType, callable $operation): mixed
    {
        $directory = dirname($this->path);

        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException('Não foi possível preparar o diretório de dados.');
        }

        $handle = fopen($this->path . '.lock', 'c');

        if ($handle === false || !flock($handle, $lockType)) {
            throw new RuntimeException('Não foi possível bloquear o arquivo de dados.');
        }

        try {
            return $operation();
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    /** @return list<array<string, mixed>> */
    private function readUnlocked(): array
    {
        if (!is_file($this->path) || filesize($this->path) === 0) {
            return [];
        }

        $content = file_get_contents($this->path);

        if ($content === false) {
            throw new RuntimeException('Não foi possível ler o arquivo de dados.');
        }

        try {
            $records = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new RuntimeException('O arquivo de dados contém JSON inválido.', previous: $exception);
        }

        if (!is_array($records)) {
            throw new RuntimeException('O arquivo de dados possui formato inválido.');
        }

        return $records;
    }

    /** @param list<array<string, mixed>> $records */
    private function writeUnlocked(array $records): void
    {
        $temporaryPath = $this->path . '.' . bin2hex(random_bytes(6)) . '.tmp';
        $content = json_encode($records, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;

        if (file_put_contents($temporaryPath, $content, LOCK_EX) === false) {
            throw new RuntimeException('Não foi possível gravar os dados temporários.');
        }

        if (!rename($temporaryPath, $this->path)) {
            @unlink($temporaryPath);
            throw new RuntimeException('Não foi possível concluir a gravação dos dados.');
        }
    }
}
