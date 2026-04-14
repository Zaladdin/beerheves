<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class CategoryRepository
{
    public function __construct(private readonly PDO $db)
    {
    }

    public function search(string $term = ''): array
    {
        $sql = 'SELECT * FROM categories';
        $params = [];

        if ($term !== '') {
            $sql .= ' WHERE name LIKE :term';
            $params['term'] = '%' . $term . '%';
        }

        $sql .= ' ORDER BY status DESC, name ASC';

        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function active(): array
    {
        $statement = $this->db->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name ASC");

        return $statement->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM categories WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);

        $category = $statement->fetch();

        return $category ?: null;
    }

    public function nameExists(string $name, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM categories WHERE name = :name';
        $params = ['name' => $name];

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
        $statement = $this->db->prepare('INSERT INTO categories (name, status) VALUES (:name, :status)');
        $statement->execute([
            'name' => $data['name'],
            'status' => $data['status'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $statement = $this->db->prepare('UPDATE categories SET name = :name, status = :status WHERE id = :id');
        $statement->execute([
            'id' => $id,
            'name' => $data['name'],
            'status' => $data['status'],
        ]);
    }

    public function deactivate(int $id): void
    {
        $statement = $this->db->prepare("UPDATE categories SET status = 'inactive' WHERE id = :id");
        $statement->execute(['id' => $id]);
    }
}
