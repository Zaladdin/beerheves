<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class ReportRepository
{
    public function __construct(private readonly PDO $db)
    {
    }

    public function stockBalances(?int $warehouseId = null, string $search = ''): array
    {
        $sql = <<<SQL
            SELECT
                sb.warehouse_id,
                sb.product_id,
                sb.qty,
                sb.updated_at,
                w.name AS warehouse_name,
                p.name AS product_name,
                p.barcode,
                p.article,
                p.unit
            FROM stock_balances sb
            INNER JOIN warehouses w ON w.id = sb.warehouse_id
            INNER JOIN products p ON p.id = sb.product_id
            WHERE p.status = 'active'
        SQL;

        $params = [];

        if ($warehouseId) {
            $sql .= ' AND sb.warehouse_id = :warehouse_id';
            $params['warehouse_id'] = $warehouseId;
        }

        if ($search !== '') {
            $sql .= ' AND (p.name LIKE :term OR p.article LIKE :term OR p.barcode LIKE :term)';
            $params['term'] = '%' . $search . '%';
        }

        $sql .= ' ORDER BY w.name ASC, p.name ASC';

        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function movementHistory(?int $productId = null, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $sql = <<<SQL
            SELECT
                d.doc_number,
                d.doc_type,
                d.status,
                d.created_at,
                di.qty,
                di.price,
                di.total,
                p.name AS product_name,
                p.barcode,
                sw.name AS source_warehouse_name,
                tw.name AS target_warehouse_name,
                u.full_name AS user_name
            FROM document_items di
            INNER JOIN documents d ON d.id = di.document_id
            INNER JOIN products p ON p.id = di.product_id
            INNER JOIN users u ON u.id = d.user_id
            LEFT JOIN warehouses sw ON sw.id = d.source_warehouse_id
            LEFT JOIN warehouses tw ON tw.id = d.target_warehouse_id
            WHERE 1 = 1
        SQL;

        $params = [];

        if ($productId) {
            $sql .= ' AND di.product_id = :product_id';
            $params['product_id'] = $productId;
        }

        if ($dateFrom) {
            $sql .= ' AND DATE(d.created_at) >= :date_from';
            $params['date_from'] = $dateFrom;
        }

        if ($dateTo) {
            $sql .= ' AND DATE(d.created_at) <= :date_to';
            $params['date_to'] = $dateTo;
        }

        $sql .= ' ORDER BY d.created_at DESC, di.id DESC LIMIT 200';

        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function salesByPeriod(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $sql = <<<SQL
            SELECT
                d.id,
                d.doc_number,
                d.created_at,
                u.full_name AS user_name,
                w.name AS warehouse_name,
                SUM(di.total) AS total_amount
            FROM documents d
            INNER JOIN document_items di ON di.document_id = d.id
            INNER JOIN users u ON u.id = d.user_id
            LEFT JOIN warehouses w ON w.id = d.source_warehouse_id
            WHERE d.doc_type = 'sale' AND d.status = 'posted'
        SQL;

        $params = [];

        if ($dateFrom) {
            $sql .= ' AND DATE(d.created_at) >= :date_from';
            $params['date_from'] = $dateFrom;
        }

        if ($dateTo) {
            $sql .= ' AND DATE(d.created_at) <= :date_to';
            $params['date_to'] = $dateTo;
        }

        $sql .= ' GROUP BY d.id, d.doc_number, d.created_at, u.full_name, w.name ORDER BY d.created_at DESC LIMIT 100';

        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function topSelling(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $sql = <<<SQL
            SELECT
                p.name AS product_name,
                p.barcode,
                SUM(di.qty) AS sold_qty,
                SUM(di.total) AS sold_total
            FROM document_items di
            INNER JOIN documents d ON d.id = di.document_id
            INNER JOIN products p ON p.id = di.product_id
            WHERE d.doc_type = 'sale' AND d.status = 'posted'
        SQL;

        $params = [];

        if ($dateFrom) {
            $sql .= ' AND DATE(d.created_at) >= :date_from';
            $params['date_from'] = $dateFrom;
        }

        if ($dateTo) {
            $sql .= ' AND DATE(d.created_at) <= :date_to';
            $params['date_to'] = $dateTo;
        }

        $sql .= ' GROUP BY p.id, p.name, p.barcode ORDER BY sold_qty DESC, sold_total DESC LIMIT 20';

        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function productOptions(): array
    {
        $statement = $this->db->query("SELECT id, name, barcode FROM products WHERE status = 'active' ORDER BY name ASC");

        return $statement->fetchAll();
    }
}
