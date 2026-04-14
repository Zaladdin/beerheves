<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\DocumentRepository;
use App\Repositories\ProductRepository;

final class DashboardController extends Controller
{
    public function index(): void
    {
        $products = new ProductRepository($this->db);
        $documents = new DocumentRepository($this->db);

        $this->render('dashboard/index', [
            'title' => 'Dashboard',
            'stats' => [
                'products' => $products->countAll(),
                'documentsToday' => $documents->countToday(),
                'salesToday' => $documents->salesTotalToday(),
            ],
            'lowStockProducts' => $products->lowStock((int) $this->config['app']['low_stock_threshold']),
            'recentDocuments' => $documents->recent(7),
        ]);
    }
}
