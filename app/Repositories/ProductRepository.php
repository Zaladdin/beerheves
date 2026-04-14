<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class ProductRepository
{
    public function __construct(private readonly PDO $db)
    {
    }

    public function search(string $term = ''): array
    {
        $sql = <<<SQL
            SELECT
                p.*,
                c.name AS category_name
            FROM products p
            LEFT JOIN categories c ON c.id = p.category_id
        SQL;

        $params = [];

        if ($term !== '') {
            $sql .= ' WHERE p.name LIKE :term OR p.article LIKE :term OR p.barcode LIKE :term';
            $params['term'] = '%' . $term . '%';
        }

        $sql .= ' ORDER BY p.status DESC, p.created_at DESC';

        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM products WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);

        $product = $statement->fetch();

        return $product ?: null;
    }

    public function findByBarcode(string $barcode, bool $activeOnly = true): ?array
    {
        $sql = <<<SQL
            SELECT
                p.*,
                c.name AS category_name
            FROM products p
            LEFT JOIN categories c ON c.id = p.category_id
            WHERE p.barcode = :barcode
        SQL;

        if ($activeOnly) {
            $sql .= " AND p.status = 'active'";
        }

        $sql .= ' LIMIT 1';

        $statement = $this->db->prepare($sql);
        $statement->execute(['barcode' => $barcode]);

        $product = $statement->fetch();

        return $product ?: null;
    }

    public function barcodeExists(string $barcode, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM products WHERE barcode = :barcode';
        $params = ['barcode' => $barcode];

        if ($ignoreId !== null) {
            $sql .= ' AND id != :ignore_id';
            $params['ignore_id'] = $ignoreId;
        }

        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return (int) $statement->fetchColumn() > 0;
    }

    public function create(array $data): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO products
                (name, barcode, article, category_id, purchase_price, sale_price, stock_qty, unit, status)
             VALUES
                (:name, :barcode, :article, :category_id, :purchase_price, :sale_price, :stock_qty, :unit, :status)'
        );

        $statement->execute([
            'name' => $data['name'],
            'barcode' => $data['barcode'],
            'article' => $data['article'] ?: null,
            'category_id' => $data['category_id'] ?: null,
            'purchase_price' => $data['purchase_price'],
            'sale_price' => $data['sale_price'],
            'stock_qty' => $data['stock_qty'] ?? 0,
            'unit' => $data['unit'],
            'status' => $data['status'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $statement = $this->db->prepare(
            'UPDATE products SET
                name = :name,
                barcode = :barcode,
                article = :article,
                category_id = :category_id,
                purchase_price = :purchase_price,
                sale_price = :sale_price,
                unit = :unit,
                status = :status
             WHERE id = :id'
        );

        $statement->execute([
            'id' => $id,
            'name' => $data['name'],
            'barcode' => $data['barcode'],
            'article' => $data['article'] ?: null,
            'category_id' => $data['category_id'] ?: null,
            'purchase_price' => $data['purchase_price'],
            'sale_price' => $data['sale_price'],
            'unit' => $data['unit'],
            'status' => $data['status'],
        ]);
    }

    public function deactivate(int $id): void
    {
        $statement = $this->db->prepare("UPDATE products SET status = 'inactive' WHERE id = :id");
        $statement->execute(['id' => $id]);
    }

    public function countAll(): int
    {
        $statement = $this->db->query("SELECT COUNT(*) FROM products WHERE status = 'active'");

        return (int) $statement->fetchColumn();
    }

    public function lowStock(int $threshold): array
    {
        $statement = $this->db->prepare(
            "SELECT p.*, c.name AS category_name
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE p.status = 'active' AND p.stock_qty <= :threshold
             ORDER BY p.stock_qty ASC, p.name ASC
             LIMIT 10"
        );
        $statement->execute(['threshold' => $threshold]);

        return $statement->fetchAll();
    }

    public function syncTotalStock(int $productId): void
    {
        $statement = $this->db->prepare(
            'UPDATE products p
             SET p.stock_qty = (
                 SELECT COALESCE(SUM(sb.qty), 0)
                 FROM stock_balances sb
                 WHERE sb.product_id = :product_id
             )
             WHERE p.id = :product_id'
        );
        $statement->execute(['product_id' => $productId]);
    }
}
