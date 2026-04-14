<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

abstract class Controller
{
    public function __construct(
        protected PDO $db,
        protected Request $request,
        protected array $config
    ) {
    }

    protected function render(string $view, array $data = [], string $layout = 'main'): void
    {
        $payload = array_merge([
            'title' => $this->config['app']['name'],
            'currentUser' => Auth::user(),
            'flashSuccess' => Session::pull('success'),
            'flashError' => Session::pull('error'),
            'errors' => Session::pull('errors', []),
            'old' => Session::pull('old', []),
            'appConfig' => $this->config['app'],
        ], $data);

        extract($payload, EXTR_SKIP);

        ob_start();
        require base_path('app/Views/' . $view . '.php');
        $content = ob_get_clean();

        require base_path('app/Views/layouts/' . $layout . '.php');
    }

    protected function redirect(string $path): never
    {
        header('Location: ' . $path);
        exit;
    }

    protected function back(): never
    {
        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/');
    }

    protected function json(array $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    protected function ensureCsrf(bool $json = false): void
    {
        $token = $this->request->post('_token') ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);

        if (Csrf::validate($token)) {
            return;
        }

        if ($json) {
            $this->json(['success' => false, 'message' => 'CSRF token is invalid.'], 419);
        }

        Session::flash('error', 'Сессия истекла. Повторите действие.');
        $this->back();
    }

    protected function redirectWithErrors(string $path, array $errors, array $old = []): never
    {
        Session::flash('errors', $errors);
        Session::flash('old', $old);
        $this->redirect($path);
    }
}
