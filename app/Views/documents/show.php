<div class="card shadow-sm border-0 mb-4">
    <div class="card-body d-flex flex-wrap gap-4 justify-content-between align-items-start">
        <div>
            <div class="section-title"><?= e($document['doc_number']) ?></div>
            <div class="text-muted"><?= e(doc_type_label($document['doc_type'])) ?>, создан <?= e(date('d.m.Y H:i', strtotime($document['created_at']))) ?></div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <span class="badge <?= e(status_badge_class($document['status'])) ?> fs-6"><?= e(document_status_label($document['status'])) ?></span>
            <a href="/documents" class="btn btn-outline-dark">К списку</a>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="info-card">
            <div class="info-label">Склад отправки</div>
            <div class="info-value"><?= e($document['source_warehouse_name'] ?: '-') ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-card">
            <div class="info-label">Склад получения</div>
            <div class="info-value"><?= e($document['target_warehouse_name'] ?: '-') ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-card">
            <div class="info-label">Пользователь</div>
            <div class="info-value"><?= e($document['user_name']) ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-card">
            <div class="info-label">Комментарий</div>
            <div class="info-value"><?= e($document['comment'] ?: '-') ?></div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
            <tr>
                <th>Barcode</th>
                <th>Товар</th>
                <th>Артикул</th>
                <th class="text-end">Qty</th>
                <th class="text-end">Цена</th>
                <th class="text-end">Сумма</th>
            </tr>
            </thead>
            <tbody>
            <?php $sum = 0; ?>
            <?php foreach ($document['items'] as $item): ?>
                <?php $sum += (float) $item['total']; ?>
                <tr>
                    <td><?= e($item['barcode']) ?></td>
                    <td><?= e($item['product_name']) ?></td>
                    <td><?= e($item['article']) ?></td>
                    <td class="text-end"><?= e(format_qty($item['qty'])) ?></td>
                    <td class="text-end">$<?= e(format_money($item['price'])) ?></td>
                    <td class="text-end">$<?= e(format_money($item['total'])) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
            <tr>
                <th colspan="5" class="text-end">Итого</th>
                <th class="text-end">$<?= e(format_money($sum)) ?></th>
            </tr>
            </tfoot>
        </table>
    </div>
</div>
