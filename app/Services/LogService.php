<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\LogRepository;
use PDO;

final class LogService
{
    private LogRepository $logs;

    public function __construct(PDO $db)
    {
        $this->logs = new LogRepository($db);
    }

    public function write(?int $userId, string $action, string $entityType, ?int $entityId = null, array $details = []): void
    {
        $this->logs->create([
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'details' => $details !== [] ? json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
        ]);
    }
}
