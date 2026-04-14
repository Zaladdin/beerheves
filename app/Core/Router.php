<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

final class Router
{
    private array $routes = [
        'GET' => [],
        'POST' => [],
    ];

    public function __construct(
        private readonly Request $request,
        private readonly PDO $db,
        private readonly array $config
    ) {
    }

    public function get(string $path, array $handler, array $options = []): void
    {
        $this->add('GET', $path, $handler, $options);
    }

    public function post(string $path, array $handler, array $options = []): void
    {
        $this->add('POST', $path, $handler, $options);
    }

    public function dispatch(): void
    {
        $method = $this->request->method();
        $path = $this->request->path();
        $route = $this->routes[$method][$path] ?? null;

        if ($route === null) {
            http_response_code(404);
            echo '<h1>404</h1><p>Страница не найдена.</p>';
            return;
        }

        if (($route['guest'] ?? false) && Auth::check()) {
            header('Location: /');
            exit;
        }

        if (($route['auth'] ?? false) && !Auth::check()) {
            header('Location: /login');
            exit;
        }

        $roles = $route['roles'] ?? [];

        if ($roles !== []) {
            if (!Auth::check()) {
                header('Location: /login');
                exit;
            }

            $userRole = Auth::user()['role'] ?? null;
            if (!in_array($userRole, $roles, true)) {
                http_response_code(403);
                echo '<h1>403</h1><p>Недостаточно прав.</p>';
                return;
            }
        }

        [$controllerClass, $action] = $route['handler'];
        $controller = new $controllerClass($this->db, $this->request, $this->config);
        $controller->{$action}();
    }

    private function add(string $method, string $path, array $handler, array $options): void
    {
        $this->routes[$method][$path] = [
            'handler' => $handler,
            'auth' => $options['auth'] ?? false,
            'guest' => $options['guest'] ?? false,
            'roles' => $options['roles'] ?? [],
        ];
    }
}
