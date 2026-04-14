<?php

declare(strict_types=1);

$config = require dirname(__DIR__) . '/config/config.php';

date_default_timezone_set($config['app']['timezone']);
session_name('beerheves_session');
session_start();

require dirname(__DIR__) . '/app/Core/helpers.php';

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    $baseDir = dirname(__DIR__) . '/app/';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (is_file($file)) {
        require $file;
    }
});

set_exception_handler(static function (Throwable $exception): void {
    $logFile = base_path('storage/logs/app.log');
    $message = sprintf(
        "[%s] %s in %s:%d\n%s\n\n",
        date('Y-m-d H:i:s'),
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(),
        $exception->getTraceAsString()
    );
    file_put_contents($logFile, $message, FILE_APPEND);

    http_response_code(500);
    echo '<h1>Server Error</h1><p>Произошла ошибка. Проверьте storage/logs/app.log.</p>';
});

$pdo = App\Core\Database::connect($config['database']);
$request = new App\Core\Request();
$router = new App\Core\Router($request, $pdo, $config);

$routeRegistrar = require dirname(__DIR__) . '/config/routes.php';
$routeRegistrar($router);
$router->dispatch();
