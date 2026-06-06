<?php

declare(strict_types=1);

namespace App\Core;

class Config
{
    private static array $data = [];

    /**
     * Parse a .env file and populate $_ENV / getenv().
     * Silently skips missing files so tests can run without one.
     */
    public static function load(string $path): void
    {
        if (! file_exists($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip comments and lines without an = sign
            if ($line === '' || $line[0] === '#' || ! str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);

            $key   = trim($key);
            $value = trim($value);

            // Strip surrounding quotes: "value" or 'value'
            if (
                strlen($value) >= 2
                && (($value[0] === '"' && $value[-1] === '"')
                    || ($value[0] === "'" && $value[-1] === "'"))
            ) {
                $value = substr($value, 1, -1);
            }

            self::$data[$key] = $value;

            // Make available via getenv() and $_ENV
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
        }
    }

    /**
     * Retrieve a configuration value.
     * Order of precedence: .env file → system environment → $default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, self::$data)) {
            return self::$data[$key];
        }

        $env = getenv($key);

        return $env !== false ? $env : $default;
    }

    /**
     * Check whether a key exists (even if its value is an empty string).
     */
    public static function has(string $key): bool
    {
        return array_key_exists($key, self::$data) || getenv($key) !== false;
    }
}
