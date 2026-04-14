<?php

declare(strict_types=1);

return [
    'app' => [
        'name' => getenv('APP_NAME') ?: 'BeerHeves ERP',
        'base_url' => rtrim((string) (getenv('APP_URL') ?: ''), '/'),
        'timezone' => getenv('APP_TIMEZONE') ?: 'Asia/Baku',
        'low_stock_threshold' => (int) (getenv('LOW_STOCK_THRESHOLD') ?: 5),
    ],
    'database' => [
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => (int) (getenv('DB_PORT') ?: 3306),
        'dbname' => getenv('DB_DATABASE') ?: 'beerheves',
        'charset' => getenv('DB_CHARSET') ?: 'utf8mb4',
        'username' => getenv('DB_USERNAME') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: '',
    ],
];
