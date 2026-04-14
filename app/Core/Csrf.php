<?php

declare(strict_types=1);

namespace App\Core;

final class Csrf
{
    private const SESSION_KEY = '_csrf_token';

    public static function token(): string
    {
        if (!Session::get(self::SESSION_KEY)) {
            Session::put(self::SESSION_KEY, bin2hex(random_bytes(32)));
        }

        return (string) Session::get(self::SESSION_KEY);
    }

    public static function validate(?string $token): bool
    {
        $sessionToken = Session::get(self::SESSION_KEY);

        if (!$sessionToken || !$token) {
            return false;
        }

        return hash_equals((string) $sessionToken, $token);
    }
}
