<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class StockBalanceRepository
{
    public function __construct(private readonly PDO $db)
    {
    }

    public function getQty(int $warehouseId, int $productId): float
    {
        $statement = $this->db->prepare(
            'SELECT COALESCE(qty, 0) FROM stock_balances WHERE warehouse_id = :warehouse_id AND product_id = :product_id LIMIT 1'
        );
        $statement->execute([
            'warehouse_id' => $warehouseId,
            'product_id' => $productId,
        ]);

        return (float) $statement->fetchColumn();
    }

    public function adjust(int $warehouseId, int $productId, float $delta): void
    {
        $statement = $this->db->prepare(
            'INSERT INTO stock_balances (warehouse_id, product_id, qty)
             VALUES (:warehouse_id, :product_id, :qty)
             ON DUPLICATE KEY UPDATE
                 qty = qty + VALUES(qty),
                 updated_at = CURRENT_TIMESTAMP'
        );

        $statement->execute([
            'warehouse_id' => $warehouseId,
            'product_id' => $productId,
            'qty' => $delta,
        ]);
    }
}
