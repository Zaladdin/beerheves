<?php $filters = $filters ?? []; ?>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form method="get" action="/reports" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Склад</label>
                <select name="warehouse_id" class="form-select">
                    <option value="0">Все склады</option>
                    <?php foreach ($warehouseOptions as $warehouse): ?>
                        <option value="<?= e($warehouse['id']) ?>" <?= selected($warehouse['id'], $filters['warehouse_id'] ?? 0) ?>><?= e($warehouse['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Товар</label>
                <select name="product_id" class="form-select">
                    <option value="0">Все товары</option>
                    <?php foreach ($productOptions as $product): ?>
                        <option value="<?= e($product['id']) ?>" <?= selected($product['id'], $filters['product_id'] ?? 0) ?>><?= e($product['name']) ?> (<?= e($product['barcode']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Дата с</label>
                <input type="date" name="date_from" class="form-control" value="<?= e($filters['date_from'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Дата по</label>
                <input type="date" name="date_to" class="form-control" value="<?= e($filters['date_to'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Поиск</label>
                <input type="text" name="search" class="form-control" placeholder="Barcode или артикул" value="<?= e($filters['search'] ?? '') ?>">
            </div>
            <div class="col-12 d-flex gap-2">
                <button class="btn btn-primary">Применить</button>
                <a href="/reports" class="btn btn-outline-dark">Сбросить</a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white">
        <div class="section-title">Остатки по складам</div>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
            <tr>
                <th>Склад</th>
                <th>Товар</th>
                <th>Barcode</th>
                <th>Артикул</th>
                <th class="text-end">Qty</th>
                <th>Ед.</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($stockBalances === []): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">Нет данных</td></tr>
            <?php endif; ?>
            <?php foreach ($stockBalances as $row): ?>
                <tr>
                    <td><?= e($row['warehouse_name']) ?></td>
                    <td><?= e($row['product_name']) ?></td>
                    <td><?= e($row['barcode']) ?></td>
                    <td><?= e($row['article']) ?></td>
                    <td class="text-end"><?= e(format_qty($row['qty'])) ?></td>
                    <td><?= e($row['unit']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-7">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white">
                <div class="section-title">История движения товара</div>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                    <tr>
                        <th>Дата</th>
                        <th>Документ</th>
                        <th>Товар</th>
                        <th>Склады</th>
                        <th class="text-end">Qty</th>
                        <th class="text-end">Сумма</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($movementHistory === []): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">Нет данных</td></tr>
                    <?php endif; ?>
                    <?php foreach ($movementHistory as $row): ?>
                        <tr>
                            <td><?= e(date('d.m.Y H:i', strtotime($row['created_at']))) ?></td>
                            <td>
                                <div class="fw-semibold"><?= e($row['doc_number']) ?></div>
                                <small class="text-muted"><?= e(doc_type_label($row['doc_type'])) ?></small>
                            </td>
                            <td>
                                <div><?= e($row['product_name']) ?></div>
                                <small class="text-muted"><?= e($row['barcode']) ?></small>
                            </td>
                            <td>
                                <small><?= e(($row['source_warehouse_name'] ?: '-') . ' -> ' . ($row['target_warehouse_name'] ?: '-')) ?></small>
                            </td>
                            <td class="text-end"><?= e(format_qty($row['qty'])) ?></td>
                            <td class="text-end">$<?= e(format_money($row['total'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-xl-5">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
                <div class="section-title">Продажи за период</div>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                    <tr>
                        <th>Документ</th>
                        <th>Пользователь</th>
                        <th>Склад</th>
                        <th class="text-end">Сумма</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($salesByPeriod === []): ?>
                        <tr><td colspan="4" class="text-center text-muted py-4">Нет данных</td></tr>
                    <?php endif; ?>
                    <?php foreach ($salesByPeriod as $row): ?>
                        <tr>
                            <td><?= e($row['doc_number']) ?></td>
                            <td><?= e($row['user_name']) ?></td>
                            <td><?= e($row['warehouse_name'] ?: '-') ?></td>
                            <td class="text-end">$<?= e(format_money($row['total_amount'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <div class="section-title">Самые продаваемые товары</div>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                    <tr>
                        <th>Товар</th>
                        <th class="text-end">Qty</th>
                        <th class="text-end">Сумма</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($topSelling === []): ?>
                        <tr><td colspan="3" class="text-center text-muted py-4">Нет данных</td></tr>
                    <?php endif; ?>
                    <?php foreach ($topSelling as $row): ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= e($row['product_name']) ?></div>
                                <small class="text-muted"><?= e($row['barcode']) ?></small>
                            </td>
                            <td class="text-end"><?= e(format_qty($row['sold_qty'])) ?></td>
                            <td class="text-end">$<?= e(format_money($row['sold_total'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
