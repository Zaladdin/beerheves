<?php
$productData = array_merge([
    'name' => '',
    'barcode' => '',
    'article' => '',
    'category_id' => '',
    'purchase_price' => '0.00',
    'sale_price' => '0.00',
    'stock_qty' => '0.000',
    'unit' => 'pcs',
    'status' => 'active',
], $product ?? [], $old ?? []);
?>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form method="post" action="<?= e($action) ?>" class="row g-4">
            <?= csrf_field() ?>

            <div class="col-md-6">
                <label class="form-label">Название</label>
                <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" value="<?= e($productData['name']) ?>" required>
                <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= e($errors['name']) ?></div><?php endif; ?>
            </div>

            <div class="col-md-3">
                <label class="form-label">Barcode</label>
                <input type="text" name="barcode" class="form-control <?= isset($errors['barcode']) ? 'is-invalid' : '' ?>" value="<?= e($productData['barcode']) ?>" required>
                <?php if (isset($errors['barcode'])): ?><div class="invalid-feedback"><?= e($errors['barcode']) ?></div><?php endif; ?>
            </div>

            <div class="col-md-3">
                <label class="form-label">Артикул</label>
                <input type="text" name="article" class="form-control" value="<?= e($productData['article']) ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label">Категория</label>
                <select name="category_id" class="form-select <?= isset($errors['category_id']) ? 'is-invalid' : '' ?>">
                    <option value="">Без категории</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= e($category['id']) ?>" <?= selected($category['id'], $productData['category_id']) ?>><?= e($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['category_id'])): ?><div class="invalid-feedback"><?= e($errors['category_id']) ?></div><?php endif; ?>
            </div>

            <div class="col-md-2">
                <label class="form-label">Цена закупки</label>
                <input type="number" step="0.01" min="0" name="purchase_price" class="form-control <?= isset($errors['purchase_price']) ? 'is-invalid' : '' ?>" value="<?= e($productData['purchase_price']) ?>">
                <?php if (isset($errors['purchase_price'])): ?><div class="invalid-feedback"><?= e($errors['purchase_price']) ?></div><?php endif; ?>
            </div>

            <div class="col-md-2">
                <label class="form-label">Цена продажи</label>
                <input type="number" step="0.01" min="0" name="sale_price" class="form-control <?= isset($errors['sale_price']) ? 'is-invalid' : '' ?>" value="<?= e($productData['sale_price']) ?>">
                <?php if (isset($errors['sale_price'])): ?><div class="invalid-feedback"><?= e($errors['sale_price']) ?></div><?php endif; ?>
            </div>

            <div class="col-md-2">
                <label class="form-label">Общий остаток</label>
                <input type="number" step="0.001" name="stock_qty" class="form-control" value="<?= e($productData['stock_qty']) ?>" readonly>
            </div>

            <div class="col-md-2">
                <label class="form-label">Ед. изм.</label>
                <input type="text" name="unit" class="form-control <?= isset($errors['unit']) ? 'is-invalid' : '' ?>" value="<?= e($productData['unit']) ?>">
                <?php if (isset($errors['unit'])): ?><div class="invalid-feedback"><?= e($errors['unit']) ?></div><?php endif; ?>
            </div>

            <div class="col-md-2">
                <label class="form-label">Статус</label>
                <select name="status" class="form-select">
                    <option value="active" <?= selected('active', $productData['status']) ?>>Активный</option>
                    <option value="inactive" <?= selected('inactive', $productData['status']) ?>>Неактивный</option>
                </select>
            </div>

            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Сохранить</button>
                <a href="/products" class="btn btn-outline-dark">Назад</a>
            </div>
        </form>
    </div>
</div>
