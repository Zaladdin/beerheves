<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center mb-4">
            <form method="get" action="/products" class="row g-2 align-items-center">
                <div class="col-auto">
                    <input type="text" name="q" class="form-control" placeholder="Поиск по названию, артикулу, barcode" value="<?= e($search) ?>">
                </div>
                <div class="col-auto">
                    <button class="btn btn-outline-dark">Поиск</button>
                </div>
            </form>
            <a href="/products/create" class="btn btn-primary">Добавить товар</a>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Название</th>
                    <th>Barcode</th>
                    <th>Артикул</th>
                    <th>Категория</th>
                    <th class="text-end">Закупка</th>
                    <th class="text-end">Продажа</th>
                    <th class="text-end">Остаток</th>
                    <th>Статус</th>
                    <th class="text-end">Действия</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($products === []): ?>
                    <tr><td colspan="10" class="text-center text-muted py-5">Товары не найдены</td></tr>
                <?php endif; ?>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?= e($product['id']) ?></td>
                        <td>
                            <div class="fw-semibold"><?= e($product['name']) ?></div>
                            <small class="text-muted"><?= e($product['unit']) ?></small>
                        </td>
                        <td><?= e($product['barcode']) ?></td>
                        <td><?= e($product['article']) ?></td>
                        <td><?= e($product['category_name'] ?? 'Без категории') ?></td>
                        <td class="text-end">$<?= e(format_money($product['purchase_price'])) ?></td>
                        <td class="text-end">$<?= e(format_money($product['sale_price'])) ?></td>
                        <td class="text-end"><?= e(format_qty($product['stock_qty'])) ?></td>
                        <td><span class="badge <?= e(status_badge_class($product['status'])) ?>"><?= e($product['status']) ?></span></td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-2">
                                <a href="/products/edit?id=<?= e($product['id']) ?>" class="btn btn-sm btn-outline-dark">Редактировать</a>
                                <form method="post" action="/products/delete">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= e($product['id']) ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" data-confirm="Деактивировать товар?">Деактивировать</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
