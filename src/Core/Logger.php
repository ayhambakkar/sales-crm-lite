<?php

declare(strict_types=1);

namespace App\Core;

use Throwable;

class Logger
{
    public static function exception(Throwable $exception): void
    {
        self::error($exception::class . ': ' . $exception->getMessage(), [
            'file'  => $exception->getFile(),
            'line'  => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }

    /**
     * @param array<string, mixed> $context
     */
    public static function error(string $message, array $context = []): void
    {
        self::write('ERROR', $message, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    private static function write(string $level, string $message, array $context = []): void
    {
        $root = defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__, 2);
        $path = $root . '/storage/logs/app.log';
        $dir  = dirname($path);

        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $encodedContext = $context === []
            ? ''
            : ' ' . json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $line = sprintf(
            "[%s] %s: %s%s\n",
            date('Y-m-d H:i:s'),
            $level,
            $message,
            $encodedContext
        );

        file_put_contents($path, $line, FILE_APPEND | LOCK_EX);
    }
}
