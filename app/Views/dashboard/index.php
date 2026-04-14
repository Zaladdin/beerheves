<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-label">Товары</div>
            <div class="stat-value"><?= e($stats['products']) ?></div>
            <div class="stat-note">Активные позиции в каталоге</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-label">Документы за сегодня</div>
            <div class="stat-value"><?= e($stats['documentsToday']) ?></div>
            <div class="stat-note">Все типы документов</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-label">Продажи за сегодня</div>
            <div class="stat-value">$<?= e(format_money($stats['salesToday'])) ?></div>
            <div class="stat-note">Проведенные продажи</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <div class="section-title">Низкий остаток</div>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                    <tr>
                        <th>Товар</th>
                        <th>Barcode</th>
                        <th class="text-end">Остаток</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($lowStockProducts === []): ?>
                        <tr><td colspan="3" class="text-center text-muted py-4">Низких остатков нет</td></tr>
                    <?php endif; ?>
                    <?php foreach ($lowStockProducts as $product): ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= e($product['name']) ?></div>
                                <small class="text-muted"><?= e($product['category_name'] ?? 'Без категории') ?></small>
                            </td>
                            <td><?= e($product['barcode']) ?></td>
                            <td class="text-end fw-semibold text-danger"><?= e(format_qty($product['stock_qty'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <div class="section-title">Последние документы</div>
                <a href="/documents" class="btn btn-sm btn-outline-dark">Все документы</a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                    <tr>
                        <th>Номер</th>
                        <th>Тип</th>
                        <th>Статус</th>
                        <th>Пользователь</th>
                        <th>Дата</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($recentDocuments === []): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">Документов пока нет</td></tr>
                    <?php endif; ?>
                    <?php foreach ($recentDocuments as $document): ?>
                        <tr>
                            <td><a href="/documents/show?id=<?= e($document['id']) ?>" class="text-decoration-none fw-semibold"><?= e($document['doc_number']) ?></a></td>
                            <td><?= e(doc_type_label($document['doc_type'])) ?></td>
                            <td><span class="badge <?= e(status_badge_class($document['status'])) ?>"><?= e(document_status_label($document['status'])) ?></span></td>
                            <td><?= e($document['user_name']) ?></td>
                            <td><?= e(date('d.m.Y H:i', strtotime($document['created_at']))) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
