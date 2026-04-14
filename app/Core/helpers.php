<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\Csrf;

function base_path(string $path = ''): string
{
    $base = dirname(__DIR__, 2);

    if ($path === '') {
        return $base;
    }

    return $base . DIRECTORY_SEPARATOR . ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR);
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function asset_url(string $path): string
{
    return '/assets/' . ltrim($path, '/');
}

function current_path(): string
{
    return parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
}

function is_active_menu(string $path): string
{
    $current = current_path();

    if ($path === '/') {
        return $current === '/' ? 'active' : '';
    }

    return str_starts_with($current, $path) ? 'active' : '';
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(Csrf::token()) . '">';
}

function selected(mixed $value, mixed $current): string
{
    return (string) $value === (string) $current ? 'selected' : '';
}

function checked(mixed $value, mixed $current): string
{
    return (string) $value === (string) $current ? 'checked' : '';
}

function format_money(mixed $value): string
{
    return number_format((float) $value, 2, '.', ' ');
}

function format_qty(mixed $value): string
{
    $formatted = number_format((float) $value, 3, '.', ' ');
    $formatted = rtrim($formatted, '0');

    return rtrim($formatted, '.');
}

function doc_type_label(string $type): string
{
    return match ($type) {
        'incoming' => 'Приход',
        'sale' => 'Продажа',
        'writeoff' => 'Списание',
        'transfer' => 'Перемещение',
        default => $type,
    };
}

function document_status_label(string $status): string
{
    return match ($status) {
        'draft' => 'Черновик',
        'posted' => 'Проведен',
        'cancelled' => 'Отменен',
        default => $status,
    };
}

function status_badge_class(string $status): string
{
    return match ($status) {
        'active', 'posted' => 'text-bg-success',
        'draft' => 'text-bg-warning',
        'inactive', 'cancelled' => 'text-bg-secondary',
        default => 'text-bg-light',
    };
}

function has_role(array $roles): bool
{
    if (!Auth::check()) {
        return false;
    }

    $user = Auth::user();

    return in_array($user['role'] ?? null, $roles, true);
}
