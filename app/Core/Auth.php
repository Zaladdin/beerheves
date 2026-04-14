<?php

declare(strict_types=1);

namespace App\Core;

final class Auth
{
    public static function login(array $user): void
    {
        Session::put('auth_user', [
            'id' => (int) $user['id'],
            'username' => $user['username'],
            'full_name' => $user['full_name'],
            'role' => $user['role'],
        ]);
        Session::regenerate();
    }

    public static function logout(): void
    {
        Session::forget('auth_user');
        Session::regenerate();
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function user(): ?array
    {
        $user = Session::get('auth_user');

        return is_array($user) ? $user : null;
    }
}
