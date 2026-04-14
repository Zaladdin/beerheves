<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\ReportRepository;
use App\Repositories\WarehouseRepository;

final class ReportController extends Controller
{
    public function index(): void
    {
        $reports = new ReportRepository($this->db);
        $warehouses = new WarehouseRepository($this->db);

        $filters = [
            'warehouse_id' => (int) $this->request->query('warehouse_id', 0),
            'search' => trim((string) $this->request->query('search', '')),
            'product_id' => (int) $this->request->query('product_id', 0),
            'date_from' => (string) $this->request->query('date_from', ''),
            'date_to' => (string) $this->request->query('date_to', ''),
        ];

        $this->render('reports/index', [
            'title' => 'Отчеты',
            'filters' => $filters,
            'warehouseOptions' => $warehouses->active(),
            'productOptions' => $reports->productOptions(),
            'stockBalances' => $reports->stockBalances($filters['warehouse_id'] ?: null, $filters['search']),
            'movementHistory' => $reports->movementHistory(
                $filters['product_id'] ?: null,
                $filters['date_from'] ?: null,
                $filters['date_to'] ?: null
            ),
            'salesByPeriod' => $reports->salesByPeriod(
                $filters['date_from'] ?: null,
                $filters['date_to'] ?: null
            ),
            'topSelling' => $reports->topSelling(
                $filters['date_from'] ?: null,
                $filters['date_to'] ?: null
            ),
        ]);
    }
}
