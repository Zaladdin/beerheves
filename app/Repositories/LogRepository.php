<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class LogRepository
{
    public function __construct(private readonly PDO $db)
    {
    }

    public function create(array $data): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO logs (user_id, action, entity_type, entity_id, details)
             VALUES (:user_id, :action, :entity_type, :entity_id, :details)'
        );

        $statement->execute([
            'user_id' => $data['user_id'],
            'action' => $data['action'],
            'entity_type' => $data['entity_type'],
            'entity_id' => $data['entity_id'],
            'details' => $data['details'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function latest(int $limit = 100): array
    {
        $statement = $this->db->prepare(
            "SELECT l.*, u.full_name AS user_name, u.role AS user_role
             FROM logs l
             LEFT JOIN users u ON u.id = l.user_id
             ORDER BY l.created_at DESC
             LIMIT :limit"
        );
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }
}
