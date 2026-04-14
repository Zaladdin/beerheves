<?php

declare(strict_types=1);

namespace App\Core;

final class Session
{
    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function put(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function pull(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION[$key] ?? $default;
        unset($_SESSION[$key]);

        return $value;
    }

    public static function flash(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }
}
