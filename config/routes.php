<?php

declare(strict_types=1);

use App\Controllers\ApiController;
use App\Controllers\AuthController;
use App\Controllers\CategoryController;
use App\Controllers\DashboardController;
use App\Controllers\DocumentController;
use App\Controllers\LogController;
use App\Controllers\ProductController;
use App\Controllers\ReportController;
use App\Controllers\WarehouseController;
use App\Core\Router;

return static function (Router $router): void {
    $router->get('/login', [AuthController::class, 'showLogin'], ['guest' => true]);
    $router->post('/login', [AuthController::class, 'login'], ['guest' => true]);

    $router->get('/', [DashboardController::class, 'index'], ['auth' => true]);
    $router->post('/logout', [AuthController::class, 'logout'], ['auth' => true]);

    $router->get('/products', [ProductController::class, 'index'], ['roles' => ['admin', 'manager']]);
    $router->get('/products/create', [ProductController::class, 'create'], ['roles' => ['admin', 'manager']]);
    $router->post('/products/store', [ProductController::class, 'store'], ['roles' => ['admin', 'manager']]);
    $router->get('/products/edit', [ProductController::class, 'edit'], ['roles' => ['admin', 'manager']]);
    $router->post('/products/update', [ProductController::class, 'update'], ['roles' => ['admin', 'manager']]);
    $router->post('/products/delete', [ProductController::class, 'delete'], ['roles' => ['admin', 'manager']]);

    $router->get('/categories', [CategoryController::class, 'index'], ['roles' => ['admin', 'manager']]);
    $router->post('/categories/store', [CategoryController::class, 'store'], ['roles' => ['admin', 'manager']]);
    $router->post('/categories/update', [CategoryController::class, 'update'], ['roles' => ['admin', 'manager']]);
    $router->post('/categories/delete', [CategoryController::class, 'delete'], ['roles' => ['admin', 'manager']]);

    $router->get('/warehouses', [WarehouseController::class, 'index'], ['roles' => ['admin', 'manager']]);
    $router->post('/warehouses/store', [WarehouseController::class, 'store'], ['roles' => ['admin', 'manager']]);
    $router->post('/warehouses/update', [WarehouseController::class, 'update'], ['roles' => ['admin', 'manager']]);
    $router->post('/warehouses/delete', [WarehouseController::class, 'delete'], ['roles' => ['admin', 'manager']]);

    $router->get('/documents', [DocumentController::class, 'index'], ['auth' => true]);
    $router->get('/documents/create', [DocumentController::class, 'create'], ['auth' => true]);
    $router->get('/documents/scan', [DocumentController::class, 'scan'], ['auth' => true]);
    $router->post('/documents/store', [DocumentController::class, 'store'], ['auth' => true]);
    $router->get('/documents/show', [DocumentController::class, 'show'], ['auth' => true]);
    $router->post('/documents/post', [DocumentController::class, 'post'], ['auth' => true]);
    $router->post('/documents/cancel', [DocumentController::class, 'cancel'], ['auth' => true]);

    $router->get('/reports', [ReportController::class, 'index'], ['roles' => ['admin', 'manager']]);
    $router->get('/logs', [LogController::class, 'index'], ['roles' => ['admin', 'manager']]);

    $router->get('/api/products/barcode', [ApiController::class, 'findByBarcode'], ['auth' => true]);
    $router->post('/api/products/quick-create', [ApiController::class, 'quickCreateProduct'], ['auth' => true]);
};
