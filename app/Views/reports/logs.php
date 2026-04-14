<div class="card shadow-sm border-0">
    <div class="card-header bg-white">
        <div class="section-title">Журнал действий</div>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
            <tr>
                <th>Дата</th>
                <th>Пользователь</th>
                <th>Действие</th>
                <th>Сущность</th>
                <th>Entity ID</th>
                <th>Details</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($logs === []): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">Логи пока отсутствуют</td></tr>
            <?php endif; ?>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= e(date('d.m.Y H:i:s', strtotime($log['created_at']))) ?></td>
                    <td>
                        <div><?= e($log['user_name'] ?: 'system') ?></div>
                        <small class="text-muted"><?= e($log['user_role'] ?: '-') ?></small>
                    </td>
                    <td><?= e($log['action']) ?></td>
                    <td><?= e($log['entity_type']) ?></td>
                    <td><?= e($log['entity_id']) ?></td>
                    <td><code class="small"><?= e($log['details']) ?></code></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
