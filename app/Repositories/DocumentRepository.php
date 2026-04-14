<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class DocumentRepository
{
    public function __construct(private readonly PDO $db)
    {
    }

    public function search(array $filters = []): array
    {
        $sql = <<<SQL
            SELECT
                d.*,
                u.full_name AS user_name,
                sw.name AS source_warehouse_name,
                tw.name AS target_warehouse_name
            FROM documents d
            INNER JOIN users u ON u.id = d.user_id
            LEFT JOIN warehouses sw ON sw.id = d.source_warehouse_id
            LEFT JOIN warehouses tw ON tw.id = d.target_warehouse_id
            WHERE 1 = 1
        SQL;

        $params = [];

        if (!empty($filters['q'])) {
            $sql .= ' AND (d.doc_number LIKE :q OR d.comment LIKE :q)';
            $params['q'] = '%' . $filters['q'] . '%';
        }

        if (!empty($filters['doc_type'])) {
            $sql .= ' AND d.doc_type = :doc_type';
            $params['doc_type'] = $filters['doc_type'];
        }

        if (!empty($filters['status'])) {
            $sql .= ' AND d.status = :status';
            $params['status'] = $filters['status'];
        }

        $sql .= ' ORDER BY d.created_at DESC';

        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function recent(int $limit = 5): array
    {
        $statement = $this->db->prepare(
            "SELECT d.*, u.full_name AS user_name
             FROM documents d
             INNER JOIN users u ON u.id = d.user_id
             ORDER BY d.created_at DESC
             LIMIT :limit"
        );
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    public function countToday(): int
    {
        $statement = $this->db->query('SELECT COUNT(*) FROM documents WHERE DATE(created_at) = CURDATE()');

        return (int) $statement->fetchColumn();
    }

    public function salesTotalToday(): float
    {
        $statement = $this->db->query(
            "SELECT COALESCE(SUM(di.total), 0)
             FROM document_items di
             INNER JOIN documents d ON d.id = di.document_id
             WHERE d.doc_type = 'sale' AND d.status = 'posted' AND DATE(d.created_at) = CURDATE()"
        );

        return (float) $statement->fetchColumn();
    }

    public function create(array $data): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO documents
                (doc_type, doc_number, source_warehouse_id, target_warehouse_id, user_id, status, comment)
             VALUES
                (:doc_type, :doc_number, :source_warehouse_id, :target_warehouse_id, :user_id, :status, :comment)'
        );

        $statement->execute([
            'doc_type' => $data['doc_type'],
            'doc_number' => $data['doc_number'],
            'source_warehouse_id' => $data['source_warehouse_id'] ?: null,
            'target_warehouse_id' => $data['target_warehouse_id'] ?: null,
            'user_id' => $data['user_id'],
            'status' => $data['status'],
            'comment' => $data['comment'] ?: null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function addItems(int $documentId, array $items): void
    {
        $statement = $this->db->prepare(
            'INSERT INTO document_items (document_id, product_id, barcode, qty, price, total)
             VALUES (:document_id, :product_id, :barcode, :qty, :price, :total)'
        );

        foreach ($items as $item) {
            $statement->execute([
                'document_id' => $documentId,
                'product_id' => $item['product_id'],
                'barcode' => $item['barcode'],
                'qty' => $item['qty'],
                'price' => $item['price'],
                'total' => $item['total'],
            ]);
        }
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare(
            "SELECT
                d.*,
                u.full_name AS user_name,
                sw.name AS source_warehouse_name,
                tw.name AS target_warehouse_name
             FROM documents d
             INNER JOIN users u ON u.id = d.user_id
             LEFT JOIN warehouses sw ON sw.id = d.source_warehouse_id
             LEFT JOIN warehouses tw ON tw.id = d.target_warehouse_id
             WHERE d.id = :id
             LIMIT 1"
        );
        $statement->execute(['id' => $id]);

        $document = $statement->fetch();

        return $document ?: null;
    }

    public function items(int $documentId): array
    {
        $statement = $this->db->prepare(
            "SELECT di.*, p.name AS product_name, p.article, p.unit
             FROM document_items di
             INNER JOIN products p ON p.id = di.product_id
             WHERE di.document_id = :document_id
             ORDER BY di.id ASC"
        );
        $statement->execute(['document_id' => $documentId]);

        return $statement->fetchAll();
    }

    public function findWithItems(int $id): ?array
    {
        $document = $this->find($id);

        if (!$document) {
            return null;
        }

        $document['items'] = $this->items($id);

        return $document;
    }

    public function updateStatus(int $id, string $status): void
    {
        $statement = $this->db->prepare('UPDATE documents SET status = :status WHERE id = :id');
        $statement->execute([
            'id' => $id,
            'status' => $status,
        ]);
    }
}
