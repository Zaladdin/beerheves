<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\LogRepository;

final class LogController extends Controller
{
    public function index(): void
    {
        $logs = new LogRepository($this->db);

        $this->render('reports/logs', [
            'title' => 'Логи',
            'logs' => $logs->latest(200),
        ]);
    }
}
