<div class="row g-4">
    <div class="col-lg-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <div class="section-title">Новый склад</div>
            </div>
            <div class="card-body">
                <form method="post" action="/warehouses/store" class="row g-3">
                    <?= csrf_field() ?>
                    <div class="col-12">
                        <label class="form-label">Название</label>
                        <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" value="<?= e($old['name'] ?? '') ?>" required>
                        <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= e($errors['name']) ?></div><?php endif; ?>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Локация</label>
                        <input type="text" name="location" class="form-control" value="<?= e($old['location'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Статус</label>
                        <select name="status" class="form-select">
                            <option value="active" <?= selected('active', $old['status'] ?? 'active') ?>>Активный</option>
                            <option value="inactive" <?= selected('inactive', $old['status'] ?? '') ?>>Неактивный</option>
                        </select>
                    </div>
                    <div class="col-12 d-grid">
                        <button class="btn btn-primary">Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form method="get" action="/warehouses" class="row g-2 mb-3">
                    <div class="col-md-8">
                        <input type="text" name="q" class="form-control" placeholder="Поиск склада" value="<?= e($search) ?>">
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-outline-dark w-100">Найти</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Название</th>
                            <th>Локация</th>
                            <th>Статус</th>
                            <th class="text-end">Действия</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if ($warehouses === []): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">Склады не найдены</td></tr>
                        <?php endif; ?>
                        <?php foreach ($warehouses as $warehouse): ?>
                            <tr>
                                <td><?= e($warehouse['id']) ?></td>
                                <td><?= e($warehouse['name']) ?></td>
                                <td><?= e($warehouse['location']) ?></td>
                                <td><span class="badge <?= e(status_badge_class($warehouse['status'])) ?>"><?= e($warehouse['status']) ?></span></td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#warehouseModal<?= e($warehouse['id']) ?>">Редактировать</button>
                                    <form method="post" action="/warehouses/delete" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= e($warehouse['id']) ?>">
                                        <button class="btn btn-sm btn-outline-danger" data-confirm="Деактивировать склад?">Деактивировать</button>
                                    </form>
                                    <div class="modal fade" id="warehouseModal<?= e($warehouse['id']) ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="post" action="/warehouses/update">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="id" value="<?= e($warehouse['id']) ?>">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Редактировать склад</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body row g-3">
                                                        <div class="col-12">
                                                            <label class="form-label">Название</label>
                                                            <input type="text" name="name" class="form-control" value="<?= e($warehouse['name']) ?>" required>
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label">Локация</label>
                                                            <input type="text" name="location" class="form-control" value="<?= e($warehouse['location']) ?>">
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label">Статус</label>
                                                            <select name="status" class="form-select">
                                                                <option value="active" <?= selected('active', $warehouse['status']) ?>>Активный</option>
                                                                <option value="inactive" <?= selected('inactive', $warehouse['status']) ?>>Неактивный</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Закрыть</button>
                                                        <button class="btn btn-primary">Сохранить</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
